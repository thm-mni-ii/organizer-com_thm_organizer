<?php

     require_once('lib/nusoap.php');



    class LSFAPI
    {
        private $endpoint;
        private $client;


        /**
         *
         * @param <type> $endpoint
         */
        function LSFAPI($endpoint)
        {
            $this->endpoint = $endpoint;
            require_once('lib/nusoap.php');

            $proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
            $proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
            $proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
            $proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';

            $timeout = 120;
            $response_timeout = 120;


            $this->client = new nusoap_client($this->endpoint, true,
                $proxyhost, $proxyport, $proxyusername, $proxypassword,$timeout,$response_timeout);

           $err = $this->client->getError();
            if ($err)
            {
                echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
                echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
                exit();
            }

        }

        /**
         *
         * @param String $query
         *
         */
        function getDataXML($query)
        {

            $para = array('name' => $query );
            $sres = $this->client->call('getDataXML',$para);
            if(!$sres)
            {
              /*echo "Fehler bei Funktionsaufruf";*/
            }


            if(strpos($query, "<all>"))
            {
            	$xml = simplexml_load_string($sres['return']);
            }
            else
            {
                $xmlheader = "<?xml version='1.0' encoding='ISO-8859-15'?>";
                $final = $xmlheader . $sres['return'];
                $xml = simplexml_load_string($final);
            }

            return $xml;
        }



    }






?>
