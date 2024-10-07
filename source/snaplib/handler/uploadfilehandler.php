<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\object\order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\rebateConfig;

class uploadfilehandler extends collectionHandler {
    function __construct(App $app) {
        $this->mapActionToRights('onFileUpload', '/all/access');
        $this->mapActionToRights('getfileTXT', '/all/access');
        $this->app = $app;
    }

    function onFileUpload($app, $params) {
        $targetFolder = 'upload/'; // Replace with the path to your desired upload folder
        
        // Check if the target folder exists
        if (!is_dir($targetFolder)) {
            // Create the target folder if it doesn't exist
            if (!mkdir($targetFolder, 0777, true)) {
                // Failed to create the folder
                $response = ['success' => false, 'message' => 'Failed to create upload folder'];
                header('Content-Type: application/json');
                echo json_encode($response);
                return;
            }
        }
        
        if (!empty($_FILES)) {
            $file = $_FILES['file'];
            $tempFile = $file['tmp_name'];
            $targetFile = $targetFolder . $file['name'];

            // Validate file extension
            $allowedExtensions = ['txt']; // Allowed file extensions
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedExtensions)) {
                // Invalid file extension
                $response = ['success' => false, 'message' => 'Only text files (.txt) are allowed'];
                header('Content-Type: application/json');
                echo json_encode($response);
                return;
            }
    
            if (move_uploaded_file($tempFile, $targetFile)) {
                // File upload successful
                $response = ['success' => true, 'message' => 'File uploaded successfully'];
            } else {
                // File upload failed
                $response = ['success' => false, 'message' => 'Failed to upload files'];
            }
        } else {
            // No file received
            $response = ['success' => false, 'message' => 'No file received'];
        }
    
        // Send JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    function getfileTXT($app,$params){
        $directory = 'upload/'; // Replace with the actual directory path where the files are uploaded
        $files = scandir($directory, SCANDIR_SORT_DESCENDING); // Get the list of files sorted by modification time
        // Find the first non-directory file
        foreach ($files as $file) {
            $filePath = $directory . $file;
            if (!is_dir($filePath)) {
                break;
            }
        }
        $data = file_get_contents($filePath);
        $lines = explode("\n", $data);
        $processedData = [];
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) == 2) {
                $value = trim($parts[0]);
                $text = trim($parts[1]);
                $processedData[] = [
                    'value' => $value,
                    'text' => $text
                ];
            }
        }
        // Return the processed data as a response
        $response = [
            'success' => true,
            'data' => $processedData // Replace with the processed data
        ];
    
        // Send the response
        header('Content-Type: application/json');
        echo json_encode($response);

    }
    
}

    
