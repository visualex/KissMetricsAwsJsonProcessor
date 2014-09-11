<?php
if( set_time_limit( 0 ) === false ){
   die("Cannot break the execution time limit, PHP is running in safe mode, please fix this before running this script.");
}

/**
* KissMetricsAwsJsonProcessor
* Processes JSON files exported from KISS Metricks Into AMAZON S3,
* This is mostly a template since every project will have its own event types and properties.
*/
class KissMetricsAwsJsonProcessor
{
   private $AmazonClient = null;
   private $getDataFromBucket = null;
   private $sendDataToBucket = null;

   /**
   * __construct
   * Initiates the class
   * @param (AmazonS3Client) ($AmazonClient) http://docs.aws.amazon.com/AWSJavaSDK/latest/javadoc/com/amazonaws/services/s3/AmazonS3Client.html
   * @param (string) (getDataFromBucket) bucket name from which you intend to pull the data
   * @param (string) (sendDataToBucket) bucket name to which you intend to push the processed data
   */
   public function __construct($AmazonClient, $getDataFromBucket, $sendDataToBucket){
      $this->AmazonClient = $AmazonClient;
      $this->getDataFromBucket = $getDataFromBucket;
      $this->sendDataToBucket = $sendDataToBucket;
   }

   // gets the file list from the import bucket
   private function getFileList(){
      // Using the high-level iterators returns ALL the objects in your bucket, low level returns truncated result of about 1000 files
      $objects = $this->AmazonClient->getIterator('ListObjects', array('Bucket' => $this->getDataFromBucket));
      return $objects;
   }

   // reads the file data
   private function getFile($key){
      // debug($key);
      $result = $this->AmazonClient->getObject(array(
          'Bucket' => $this->getDataFromBucket,
          'Key'    => $key
      ));
      $text = (string) $result['Body']; //cast this EntityBody object as a string
      return $text; 
   }

   // process our json, this is an example, do here what you need to with your json
   private function processFile($fileContentString){
      //KISS Metricks export a JSON Object per line in a file
      $finalProcessedFile = '';
      $linesInTheFile = explode("\n", $fileContentString);
      foreach ($linesInTheFile as $key => $lineInTheFile) {
         $json    = json_decode($lineInTheFile);
         // process your JSON here
         // ...
         // ... if ( ) { ... }
         // ...
         // send it back to a string format
         $finalProcessedFile  .= json_encode($json) . "\n";
      }
      return $finalProcessedFile;
   }

   // saves a file to our export bucket
   private function saveFile($id, $fileContentString){
      $result = $this->AmazonClient->putObject(array(
          'Bucket' => $this->sendDataToBucket,
          'Key'    => $id,
          'Body'   => $fileContentString
      ));
      return $result;
   }

   // main public method of this class
   public function processJsonFiles(){
      $filesList = $this->getFileList();
      foreach ($filesList as $key => $file) {
         //do this at your will
         if( !preg_match('/.json/', $file['Key'] ) ){
            continue;
         }
         $fileContents = $this->getFile($file['Key']);
         $fileProcessed = $this->processFile($fileContents);
         $this->saveFile($file['Key'], $fileProcessed);
      }
   }
}

//you can use the PHAR / Compozer / Zipped library
require 'aws/aws-autoloader.php';
use Aws\Common\Aws;
// Create the AWS service builder, providing the path to the config file
$aws = Aws::factory('./config.php'); 
$s3client = $aws->get('s3');

$KissMetricsAwsJsonProcessor = new KissMetricsAwsJsonProcessor($s3client, 'bucketWithJson', 'bucketForNewProcessedJson');
$KissMetricsAwsJsonProcessor->processJsonFiles();



