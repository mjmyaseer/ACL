<?php

namespace domain\Libs;
use Google\Cloud\Storage\StorageClient;

class InternalHelpers
{
    public function cdn_image_upload($source_file, $destination_file)
    {
        $cdn_ip = (env('CDN_URL'));
        $cdn_port = (env('CDN_PORT'));
        $cdn_un = (env('CDN_UN'));
        $cdn_pw = (env('CDN_PW'));

        try {
            $ip = $cdn_ip;
            $port = $cdn_port;
            $timeout = '360';
            $un = $cdn_un;
            $pw = $cdn_pw;

            $conn_id = ftp_connect($ip, $port, $timeout)
            or die ("Can't connect to FTP Server : $ip");
            $login_result = ftp_login($conn_id, $un, $pw);

            if ((!$conn_id) || (!$login_result)) {
                print "FTP connection failed!";
                exit();
            }

            // turn on passive mode transfers
            if (ftp_pasv($conn_id, true) == FALSE) {
                print "Passive FTP connection failed!";
                exit();
            }

            ftp_pasv($conn_id, true);

            $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

            unlink($source_file);

            ftp_close($conn_id);
        } catch (Exception $e) {
            echo "<pre>";
            print_r('Invalid Credentials');
            exit;
        }
    }

    public function cdn_bucket_upload($path, $destination_file)
    {
        $bucketName = (env('CDN_BUCKET_NAME'));
        $projectId = (env('CDN_PROJECT_ID'));
        $storage = new StorageClient(
            [
                'keyFilePath' => (env('CDN_KEY_PATH')),
                'projectId' => $projectId,
            ]
        );

        $file = fopen($path, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
            'name' => $destination_file
        ]);

    }

    /**
     * @param $base64_string
     * @param $output_file
     * @return mixed
     * Converting the base64 image and saving to db
     */
    public function base64_to_jpeg($base64_string, $output_file)
    {
        if(!file_exists(storage_path().'/image_temp'))
        {
            mkdir(storage_path().'/image_temp', 0777, true);
        }
        // open the output file for writing
        $ifp = fopen($output_file, 'wb');

        // split the string on commas
        $data = explode(',', $base64_string);

        // we could add validation here with ensuring count( $data ) > 1
        fwrite($ifp, base64_decode($data[1]));

        // clean up the file resource
        fclose($ifp);

        return $output_file;
    }

    public static function sidebar()
    {
        $self = new self();
        $result = $self->getRedeemTotal();
        
    $data['logos'] = '456456';

    return $result;
    }

    public function getRedeemTotal(){
        return $this->home->getRedeemTotal();
    }

}