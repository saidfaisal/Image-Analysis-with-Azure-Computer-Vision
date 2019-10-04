<!doctype html>
<html lang="en">
  <head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.css">  
    <!-- Font Awesome Icons -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Plugin CSS -->
    <link href="vendor/magnific-popup/magnific-popup.css" rel="stylesheet">
    <!-- Theme CSS - Includes Bootstrap -->
    <link href="css/creative.min.css" rel="stylesheet">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
  </head>

  <body>
    
    <!-- Main -->
    <div class="container-fluid bg-info pt-4">
      <div class="container">
        <section class="row" id="showcase">
          <div class="col p-3 text-light mb-5">
            <h1>Analyze Image</h1>
            <form action="#" method="post" enctype="multipart/form-data" class="mt-2">
              <input type="file" name="fileToUpload" id="fileToUpload" class="btn btn-warning text-dark">
              <input type="submit" value="Upload" name="submit" class="btn btn-dark text-warning">
            </form>
<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
$url = "https://example.com";
if(isset($_POST['submit'])){
$target_file = $_FILES["fileToUpload"]["tmp_name"];
$upload = true;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$connectionString = "DefaultEndpointsProtocol=https;AccountName=".getenv('ACCOUNT_NAME').";AccountKey=".getenv('ACCOUNT_KEY');
$blobClient = BlobRestProxy::createBlobService($connectionString);

if($target_file != "") {
    echo "Image '".basename($_FILES["fileToUpload"]["name"])."' has been uploaded.";
}

$fileToUpload = $target_file;

if (!isset($_GET["Cleanup"])) {
    // Create container options object.
    $createContainerOptions = new CreateContainerOptions();
    $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // Set container metadata.
    $createContainerOptions->addMetaData("key1", "value1");
    $createContainerOptions->addMetaData("key2", "value2");

    $containerName = "blockblobs".generateRandomString();

    try {
        // Create container.
        $blobClient->createContainer($containerName, $createContainerOptions);

        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix($target_file);

        $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
        fclose($myfile);
        
        $content = fopen($fileToUpload, "r");
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                $url = $blob->getUrl();
            }
        
            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
    }
    catch(ServiceException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
}}
?>
            <h4 class="mt-3">Blob Present : </h4>
            <button class="btn btn-outline-light badge badge-pill" onclick="processImage()">Analyze Image Now &raquo;</button>
            <p class="mt-3 d-inline" id="url"><?php echo $url?></p>
          </div>
        </section>
      </div>    
    </div>

<script type="text/javascript">
    function processImage() {
        var subscriptionKey = "cc8e007b34cb48639d743ce7ec3befa5";
        var uriBase ="https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
 
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        var sourceImageUrl = document.getElementById("url").innerHTML;
        if(sourceImageUrl != "https://example.com") {
            document.querySelector("#sourceImage").src = sourceImageUrl;
        }
        $.ajax({
            url: uriBase + "?" + $.param(params),

            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").val(JSON.stringify(data, null, 2));
            var myJSON = JSON.stringify(data);
            var json = JSON.parse(String(myJSON));
            document.getElementById("response").innerHTML = json.description.captions[0].text;
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };
</script>

    <div class="container-fluid mt-5 mb-5">
      <div class="container">
        Source image:
        <div style="width:820px; display:table-cell;">
            <img id="sourceImage" width="800" class="img-fluid w-25 mt-2"/>
        </div>
        Response:
        <h4 class="font-weight-bold text-dark" id="response">No image founded, please choose an image.</h4> 
      </div>
    </div>

   <!-- Footer -->
  <footer class="bg-dark py-4">
    <div class="container">
      <div class="small text-center text-muted">Copyright &copy; 2019 - Designed By Said Faisal</div>
    </div>
  </footer>
      
    <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  </body>
</html>