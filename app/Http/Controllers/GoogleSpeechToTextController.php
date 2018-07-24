<?php

namespace App\Http\Controllers;

ini_set("upload_max_filesize","100M");
ini_set("post_max_size", "100M");
ini_set('memory_limit', "1024M");

use App\FileUtils;
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Speech\Operation;
use Google\Cloud\Speech\V1p1beta1\LongRunningRecognizeResponse;
use Google\Cloud\Speech\V1p1beta1\RecognitionAudio;
use Google\Cloud\Speech\V1p1beta1\RecognitionConfig;
use Google\Cloud\Speech\V1p1beta1\SpeechClient;
use Google\Cloud\Speech\V1p1beta1\SpeechRecognitionAlternative;
use Google\Cloud\Speech\V1p1beta1\SpeechRecognitionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
include 'rest7.php';

/**
 * Class GoogleSpeechToTextController
 *
 * Parses a sound file input and returns a page showing the recognized text results.
 *
 * @package App\Http\Controllers
 */
class GoogleSpeechToTextController extends Controller
{

    public function results(Request $request){

        // init google speech client
        $speechClient = new SpeechClient([
            'projectId' => env('GOOGLE_APPLICATION_PROJECT_ID'),
            'languageCode' => 'en-US',
        ]);

        $file = $request->file('file'); //<-- we need to pass contents of this file.

        $fileFormat = FileUtils::getMimeFileFormat($file->getMimeType());
        $fileContent = null;

        /*
         * Google cloud throws a fit if the file isn't wav or flac; sorry mp3.
         * https://cloud.google.com/speech-to-text/docs/best-practices
         *
         * If we didn't receive wav, convert to wav
         */
        if($fileFormat != 'wav'){

            // rest7.com has a free audio web conversion service
            $url = 'http://api.rest7.com/v1/sound_convert.php?format=wav';
            $input = file_get_contents($file);
            $data = json_decode(_uploadFile7($url, $input, $file->getFilename()));

            if($data->success == 1){ // request was successful
                $filePath = $data->file;
                $fileContent = file_get_contents($filePath); // download file
            }

        }else{
            $fileContent = file_get_contents($file);
        }

        if($fileContent){

            // upload to gcs
            $disk = Storage::disk('gcs');

            $randId = md5(time() . rand()).'.wav';

            $disk->put($randId, $fileContent);
            $gcsUrl = 'gs://'.env('GOOGLE_CLOUD_STORAGE_BUCKET').'/'.$randId;

            // config for recognition
            $config = new RecognitionConfig();
            $config->setEnableAutomaticPunctuation(true)
                ->setUseEnhanced(true)
                ->setLanguageCode('en-US')
                ->setModel('video'); //<- video model is more accurate for use case.

            // create recognition audio from google cloud storage url
            $recognitionAudio = new RecognitionAudio();
            $recognitionAudio->setUri($gcsUrl);

            // begin operation
            $transcriptionOperation = $speechClient
                ->longRunningRecognize($config, $recognitionAudio);

            // wait for operation to complete
            $backoff = new ExponentialBackoff(10);
            $backoff->execute(function () use ($transcriptionOperation) {
                $transcriptionOperation->reload();
                if (!$transcriptionOperation->isDone()) {
                    throw new \Exception('still working', 404);
                }
            });


            // show results.
            $results =  $transcriptionOperation->isDone() && $transcriptionOperation->operationSucceeded() ?
                $transcriptionOperation->getResult()->getResults() : null;
            return view('results', compact('results'));
        }else{
            return view('results');
        }
    }

}
