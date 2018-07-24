<?php

namespace App\Http\Controllers;

use App\FileUtils;
use Google\Cloud\Core\ExponentialBackoff;
use Google\Cloud\Storage\StorageObject;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Google\Cloud\Speech\SpeechClient;
use Illuminate\Support\Facades\Storage;
use \RobbieP\CloudConvertLaravel\Facades\CloudConvert;
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

            $transcriptionOptions = [
                'languageCode' => 'en-US',
                'enableWordTimeOffsets' => false,
                'enableAutomaticPunctuation' => true,
                'model' => 'phone_call',
                'useEnhanced' => true
            ];

            // begin operation
            $transcriptionOperation = $speechClient->beginRecognizeOperation($gcsUrl, $transcriptionOptions);

            // wait for operation to complete
            $backoff = new ExponentialBackoff(10);
            $backoff->execute(function () use ($transcriptionOperation) {
                $transcriptionOperation->reload();
                if (!$transcriptionOperation->isComplete()) {
                    throw new \Exception('still working', 500);
                }
            });

            // show results.
            $transcriptions = $transcriptionOperation->isComplete() ? $transcriptionOperation->results() : null;

            return view('results', compact('transcriptions'));
        }else{
            return view('results');
        }
    }

}
