<?php
/**
* MediaEngine.php - Main component file
*
* This file is part of the Media component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Media;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Media\Interfaces\MediaEngineInterface;
use File;
use ImageIntervention;
use YesFileStorage;

class MediaEngine extends BaseEngine implements MediaEngineInterface
{
    protected $currentDisk;
    protected $disk;
    protected $elements;
    /**
     * Constructor.
     *
     * @param  MediaRepository  $mediaRepository - Media Repository
     *-----------------------------------------------------------------------*/
    public function __construct()
    {
        $this->currentDisk = configItem('current_filesystem_driver');
        $this->disk = YesFileStorage::on($this->currentDisk); // do_s3_space, local
        $this->elements = config('yes-file-storage.element_config');
    }

    /**
     * Upload all files
     *
     * @param  array  $input
     * @param  number  $allowedExtensions
     * @return response array
     *-----------------------------------------------------------------------*/
    public function processUpload($input, $folderPath, $requestFor = '', $storeAsPublic = true)
    {
        try {
            $file = $input['filepond'];

            $fileOriginalName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileBaseName = str_slug(basename($fileOriginalName, '.'.$fileExtension));
            $fileName = $fileBaseName.".$fileExtension";

            // if requested $requestFor not present in $this->elements
            // Then it return invalid request message.
            if (! array_has($this->elements, $requestFor)) {
                return $this->engineReaction(2, null, 'Something went wrong.');
            }

            $restrictions = $this->elements[$requestFor]['restrictions'];
            $allowedFileTypes = $restrictions['allowedFileTypes'];

            // Check restrictions of file
            if (! in_array("image/$fileExtension", $allowedFileTypes)) {
                return $this->engineReaction(2, null, __tr('Only __ex__ images accepted.', [
                    '__ex__' => implode(',', $allowedFileTypes),
                ]));
            }
            // If not exists then folder then create
            if ($this->disk->isExists($folderPath) === false) {
                // create temp file folder
                $this->disk->createFolder($folderPath);
            }

            // Check if file already exists then delete file first
            if ($this->disk->isExists($folderPath.'/'.$fileName)) {
                // $fileName = $fileBaseName."-".uniqid().".$fileExtension";
                $this->delete($folderPath, $fileName);
            }

            // Store file on destination
            if ($this->disk->storeFileAs($folderPath, $file, $fileName)) {
                if ($storeAsPublic) {
                    $this->disk->setFileAccessType($folderPath.'/'.$fileName, 'public');
                }

                return $this->engineReaction(1, [
                    'path' => getMediaUrl($folderPath, $fileName),
                    'original_filename' => $fileOriginalName,
                    'fileName' => $fileName,
                    'fileExtension' => $fileExtension,
                    'realPath' => $folderPath,
                ], __tr('File uploaded successfully.'));
            }

            return $this->engineReaction(2, null, __tr('Something went wrong, Please try again.'));

            // catch exception
        } catch (Exception $e) {
            return $this->engineReaction(2, null, $e->getMessage());
        }
    }

    /**
     * Process store favicon media.
     *
     * @param  string  $logoImageFile
     * @return bool
     *---------------------------------------------------------------- */
    public function processMoveFile($destinationPath, $fileName, $resizeOptions = [])
    {
        try {
            $tempFolderPath = getPathByKey('user_temp_uploads', ['{_uid}' => authUID()]);

            // Check exists of file
            if ($this->disk->isExists($tempFolderPath.'/'.$fileName) === false) {
                return $this->engineReaction(2, [], __tr('File does not exists.'));
            }

            // full source path
            $sourcePath = $tempFolderPath.'/'.$fileName;

            // Check if media directory exist
            if ($this->disk->isExists($destinationPath) === false) {
                // create temp file folder
                $this->disk->createFolder($destinationPath);
            }

            // If source moved to destination
            if ($this->disk->moveFile($sourcePath, $destinationPath.'/'.$fileName)) {
                if (! __isEmpty($resizeOptions)) {
                    // $this->resize($destinationPath, $fileName, $resizeOptions);
                    $this->resizeImageAndUpload($destinationPath, $fileName, $resizeOptions);
                }

                // return file preview url and file name
                return $this->engineReaction(1, [
                    'path' => getMediaUrl($destinationPath, $fileName),
                    'fileName' => $fileName,
                ]);
            }
        } catch (Exception $e) {
            return $this->engineReaction(2, [], __tr('Something went wrong while move file.'));
        }
    }

    /**
     * Delete file
     *
     * @return array
     *---------------------------------------------------------------- */
    public function delete($destinationPath, $filename = null, $additionalOptions = [])
    {
        try {
            if ($filename) {
                $destinationPath .= '/'.$filename;
            }

            // Delete existing file
            if ($this->disk->isExists($destinationPath)) {
                if ($this->disk->deleteFile($destinationPath)) {
                    if (
                        isset($additionalOptions['thumbnail_space_path'])
                        and ! __isEmpty($additionalOptions['thumbnail_space_path'])
                    ) {
                        $thumbnailSpacePath = array_get($additionalOptions, 'thumbnail_space_path');
                        if ($this->disk->isExists($thumbnailSpacePath.'/'.$filename)) {
                            $this->disk->deleteFile($thumbnailSpacePath.'/'.$filename);
                        }
                    }

                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Process Upload Temp Media
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadTempMedia($inputFile, $requestFor)
    {
        $tempUploadFolderPath = getPathByKey('user_temp_uploads', ['{_uid}' => authUID()]);

        return $this->processUpload($inputFile, $tempUploadFolderPath, $requestFor);
    }

    /**
     * Process Upload Logo
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadLogo($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('logo');

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor);
    }

    /**
     * Process Upload Logo
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadSmallLogo($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('small_logo');

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor);
    }

    /**
     * Process Upload Logo
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadFavicon($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('favicon');

        return $this->processUpload($inputFile, $logoFolderPath, $requestFor);
    }

    /**
     * Process Upload File on local server
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadFileOnLocalServer($input, $allowedExtension = '')
    {
        // if request file not found it will throw error.
        if (! array_has($input, 'filepond') && __isEmpty($input['filepond'])) {
            return $this->engineReaction(2, null, __tr('Uploaded file does not exists'));
        }

        $uploadedFile = $input['filepond'];

        // Check if file __isEmpty or is valid
        if (__isEmpty($uploadedFile) and ! $uploadedFile->isValid()) {
            return $this->engineReaction(2, null, __tr('invalid uploaded file.'));
        }

        $fileOriginalName = $uploadedFile->getClientOriginalName();
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $fileBaseName = str_slug(basename($fileOriginalName, '.'.$fileExtension));
        $fileName = $fileBaseName.'-'.uniqid().'.'.$fileExtension;

        $restrictions = $this->elements[$allowedExtension]['restrictions'];
        $allowedFileTypes = $restrictions['allowedFileTypes'];

        // Check restrictions of file
        if (! in_array("image/$fileExtension", $allowedFileTypes)) {
            return $this->engineReaction(2, null, __tr('Only __ex__ images accepted.', [
                '__ex__' => implode(',', $allowedFileTypes),
            ]));
        }

        $path = getPathByKey('user_temp_uploads', ['{_uid}' => authUID()]);

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }

        if ($uploadedFile->move($path, $fileName)) {
            return $this->engineReaction(1, [
                'fileName' => $fileName,
            ], __tr('File Uploaded Successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Something went wrong while file uploading.'));
    }

    /**
     * Process Upload Profile Image
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadProfile($inputFile, $requestFor)
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);

        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $profileImageFolderPath = getPathByKey('profile_photo', ['{_uid}' => authUID()]);

            return $this->resizeImageAndUpload($profileImageFolderPath, $fileName, [
                'height' => 360,
                'width' => 360,
            ]);

            return $this->engineReaction(2, null, __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Resize image and upload on server
     *
     * @return array
     *---------------------------------------------------------------- */
    public function resizeImageAndUpload($destinationPath, $fileName, $options = [])
    {
        $path = getPathByKey('user_temp_uploads', ['{_uid}' => authUID()]);

        // create path to thumbnail
        $localFileDestination = $path.'/'.$fileName;

        // open an image file
        $thumbnail = ImageIntervention::make($localFileDestination);

        $width = $options['width'];
        $height = $options['height'];

        // now you are able to resize the instance
        $thumbnail->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // finally we save the image as a new image
        $thumbnail->save($localFileDestination);

        if ($this->disk->isExists($destinationPath) === false) {
            // create temp file folder
            $this->disk->createFolder($destinationPath);
        }

        if ($this->disk->storeFileAs($destinationPath, $localFileDestination, $fileName)) {
            // Delete file from local server
            if (File::exists($localFileDestination)) {
                File::delete($localFileDestination);
            }
            // set the visibility to public so the image can be available directly without creds
            if (!$this->disk->setFileAccessType($destinationPath.DIRECTORY_SEPARATOR.$fileName, 'public')) {
                // delete the file if it fails to set the visibility as public
                $this->disk->deleteFile($destinationPath.DIRECTORY_SEPARATOR.$fileName);
                return $this->engineReaction(2, null, __tr('Failed to set file as public.'), 500);
            }

                // return file preview url and file name
                return $this->engineReaction(1, [
                    'path' => getMediaUrl($destinationPath, $fileName),
                    'fileName' => $fileName,
                    'visibility' => $destinationPath.'/'.$fileName,
                ], __tr('File Uploaded successfully.'));
        }

        return $this->engineReaction(2, null, __tr('Something went wrong while file moving.'), 500);
    }

    /**
     * Process Upload Profile Image
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadCoverPhoto($inputFile, $requestFor)
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);

        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $coverPhotoFolderPath = getPathByKey('cover_photo', ['{_uid}' => authUID()]);

            return $this->resizeImageAndUpload($coverPhotoFolderPath, $fileName, [
                'height' => 312,
                'width' => 820,
            ]);

            return $this->engineReaction(2, null, __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Process upload user photos
     *
     * @return array
     *---------------------------------------------------------------- */
    public function uploadUserPhotos($inputFile, $requestFor)
    {
        $uploadedFileOnLocalServer = $this->processUploadFileOnLocalServer($inputFile, $requestFor);

        if ($uploadedFileOnLocalServer['reaction_code'] == 1) {
            $fileName = $uploadedFileOnLocalServer['data']['fileName'];
            $userPhotosFolderPath = getPathByKey('user_photos', ['{_uid}' => authUID()]);

            return $this->resizeImageAndUpload($userPhotosFolderPath, $fileName, [
                'height' => 820,
                'width' => 312,
            ]);

            return $this->engineReaction(2, null, __tr('Something went wrong while file moving.'));
        }

        return $uploadedFileOnLocalServer;
    }

    /**
     * Delete media image
     *
     * @param  number  $productID
     * @return bool
     *---------------------------------------------------------------- */
    public function processDeleteFile($destinationPath, $filename = null)
    {
        $imageMediaPath = $destinationPath.'/'.$filename;

        // Check if image media exist & is deleted successfully
        if (File::exists($imageMediaPath) and File::delete($imageMediaPath)) {
            return true;
        }

        return false;
    }

    /**
     * Delete user all account data
     *
     * @return array
     *---------------------------------------------------------------- */
    public function deleteUserAccount()
    {
        $userAccountFolderPath = getPathByKey('user', ['{_uid}' => getUserUID()]);

        return $this->disk->deleteFolder($userAccountFolderPath);
    }

    /**
     * Process Upload Logo
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processUploadTranslationFile($inputFile, $requestFor)
    {
        $logoFolderPath = getPathByKey('language_file');
        $this->disk = YesFileStorage::on('local');
        $uploadResult = $this->processUpload($inputFile, $logoFolderPath, $requestFor);
        $this->disk = YesFileStorage::on($this->currentDisk);

        return $uploadResult;
    }
}
