# Google Cloud Speech-To-Text Sample

## Background
Google Cloud's [Speech-To-Text API](https://cloud.google.com/speech-to-text/) let's you transcribe audio files into text
using machine learning and Artificial intelligence. This is a simple sample web-page consuming that API as a service.

## Example
You can find an example project running at [http://joeydalu.herokuapp.com/samples/googlespeech](http://joeydalu.herokuapp.com/samples/googlespeech)
- Please use files less than **700kb**.

## Technologies
The entire project is a PHP web-app built using the [Laravel]() framework. This project also uses:
- Google Cloud Platform
    - [Google Speech-To-Text API](https://cloud.google.com/speech-to-text/): For transcribing audio files to text
    - [Google Cloud Storage](https://cloud.google.com/storage/): For storing audio files prior to transcription. We use this to serve files to the API
    
- Rest7 (Free Web Services)
    - [Sound Convert API](http://rest7.com/sound_convert): For converting files into _wav_ format as Google's Speech API [only works with a few audio formats and codecs](https://cloud.google.com/speech-to-text/docs/encoding).
    

## Installation
The project is easy to setup provided you have all the requirements. See below and yonder.

### Requirements

#### Google Cloud Platform account: 
- Visit your [Google Cloud Console](https://console.cloud.google.com/). You can create an account if you don't already have one. If you already have an account, you can 
easily just sign-in with it to start using the service.
- [Create a new project](https://console.cloud.google.com/projectcreate). You can name it anything, it doesn't matter.
- Enable the **Cloud Speech API** under [API's and Services](https://console.cloud.google.com/apis/library). 
- Finally, [create a Google Storage bucket](https://console.cloud.google.com/storage/create-bucket) for your application. You can also just use an existing one. 

#### Laravel PHP:
- Follow [this link](https://laravel.com/docs/5.6/installation) to install laravel on your pc if you don't have it.

#### Source Code:
Just clone this to your preferred directory, maybe via git? 

`git clone https://github.com/josephdalughut/speechtotext.git`

### Setup

#### 1. Google Project Access
- cd into the project directory and run `composer install` to install any dependencies required by the project which you don't have yet.
- Follow the instructions [here](https://cloud.google.com/compute/docs/access/create-enable-service-accounts-for-instances) to create a new **Service Account** for yout Google Cloud Project.
Make sure to make the account a **Project** > **Owner**. It should download a *json-key* file which you'll use in the next step.
- Place the downloaded `.json` file in the *private* folder at the root level of the application source code. If the folder doesn't exist, 
you can just create it.
- Rename the json file to *gcp_service_account.json*


#### 2. `.env` File
- Rename the **.env.example** file contained in the root folder to **.env**
- Add the following environment variables to the bottom of the file:
    - GOOGLE_APPLICATION_CREDENTIALS=../private/gcp_service_account.json
    - GOOGLE_APPLICATION_PROJECT_ID=*your Google cloud project ID*
    (you can find this *Project ID* on the dashboard of your Google Cloud Console)
    - GOOGLE_CLOUD_STORAGE_BUCKET=*your Google cloud storage bucket name*
    
#### 3. Run the Web-app
Once you're done with all the setup above, it's time to run your application:
- run `php artisan serve` to start serving the application.
- Open your web-browser and navigate to `localhost:8000` to test out the application.
