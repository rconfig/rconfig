<?php

namespace App\Http\Controllers\Connections\Telnet;

class Read
{
    // private $filterarray = [
    //     'Access',
    //     '     ',
    //     '--More--',
    //     'chr(8)'
    // ];
    protected $connection;

    protected $cliDebugStatus;

    protected $pagerPrompt;

    private $character;

    protected $data;

    private $prompt;

    protected $send;

    public function __construct($connectionObj)
    {
        $this->connection = $connectionObj->connection;
        $this->cliDebugStatus = $connectionObj->cliDebugStatus;
        $this->pagerPrompt = $connectionObj->pagerPrompt;
    }

    /**
     * Read from socket until $prompt
     *
     * @param  string  $prompt Single character or string
     */
    public function readTo($prompt)
    {
        $this->prompt = $prompt;
        $this->errorIfNoConnection();

        while (($this->character = fgetc($this->connection)) !== false) {
            $this->data .= $this->character;
            if ($this->readToPrompt()) {
                break;
            }
            // if($this->cliDebugStatus) {echo $this->character;}

        }

        return $this->readToPrompt(); // this will end while loop on a match
    }

    private function readToPrompt()
    {
        if (preg_match('/' . $this->prompt . '$/', $this->data)) {
            if ($this->cliDebugStatus) {
                dump($this->data);
            }
            return true;
        }
        // $this->keystrokeOnMatchToPageDownConfig();
        // $promptLength = gmp_strval(gmp_neg(strlen($this->prompt)));
        // if (substr(trim($this->data), $promptLength) === $this->prompt)
        // {
        //     if($this->cliDebugStatus) { dump($this->data); }
        //     return true;
        // }
    }

    public function getDataStream()
    {
        return $this->data;
    }

    private function errorIfNoConnection()
    {
        if (!$this->connection) {
            throw new \Exception('Telnet connection failed');
        }
    }

    // public function keystrokeOnMatchToPageDownConfig()
    // {
    //     // dump(strpos($this->data, '--More--'));
    //     if (strpos($this->data, $this->pagerPrompt) != false) {
    //         $this->send = new Send($this->connection);
    //         $this->send->sendString("   ");
    //     }
    // }

    /* FOR FUTURE USE */
    // private function filterData()
    // {
    //     array_map(function ($filter){
    //         $this->data = str_replace($filter, "", $this->data);
    //     }, $this->filterarray);
    // }
}
