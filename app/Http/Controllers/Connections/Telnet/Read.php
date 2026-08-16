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
    private $character;
    protected $data;
    private $prompt;
    private $promptPattern;

    public function __construct($connectionObj)
    {
        $this->connection = $connectionObj->connection;
        $this->cliDebugStatus = $connectionObj->cliDebugStatus;
    }

    /**
     * Read from socket until $prompt
     *
     * @param  string  $prompt  Single character or string
     */
    public function readTo($prompt)
    {
        $this->prompt = $prompt;
        $this->promptPattern = $this->buildPromptPattern((string) $prompt);
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

    /**
     * Build the pattern that spots the prompt at the tail of the read buffer.
     *
     * The prompt is free text taken from the device record, and `/` is the delimiter
     * here. A prompt such as `admin@sw1/config#` used to close the pattern early, so
     * every preg_match() failed and the read ran until the socket died rather than
     * matching. Escape the delimiter only, which is what the SSH path does for its own
     * `~` delimiter, so prompts deliberately written as patterns keep working. If the
     * escaped prompt still will not compile, match the prompt literally instead of
     * leaving the caller with a pattern that can never match.
     */
    private function buildPromptPattern(string $prompt): string
    {
        $pattern = '/' . str_replace('/', '\/', $prompt) . '$/';

        set_error_handler(static fn (): bool => true);

        try {
            $compiles = preg_match($pattern, '') !== false;
        } finally {
            restore_error_handler();
        }

        return $compiles ? $pattern : '/' . preg_quote($prompt, '/') . '$/';
    }

    /**
     * A prompt written as plain text is the common case, and plain text is allowed to
     * contain regex metacharacters: `[admin@MikroTik] /interface>` reads as a character
     * class followed by a literal, which compiles happily and then never matches. Take
     * the literal tail first so those prompts work as typed, and fall through to the
     * pattern so prompts deliberately written as regex keep working too. An empty prompt
     * matches on the pattern, as it always has.
     */
    private function promptMatched(): bool
    {
        if ($this->prompt !== '' && $this->prompt !== null && str_ends_with((string) $this->data, (string) $this->prompt)) {
            return true;
        }

        return (bool) preg_match($this->promptPattern, (string) $this->data);
    }

    private function readToPrompt()
    {
        if ($this->promptMatched()) {
            if ($this->cliDebugStatus) {
                dump($this->data);
            }

            return true;
        }
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
        if (! $this->connection) {
            throw new \Exception('Telnet connection failed');
        }
    }

    /* FOR FUTURE USE */
    // private function filterData()
    // {
    //     array_map(function ($filter){
    //         $this->data = str_replace($filter, "", $this->data);
    //     }, $this->filterarray);
    // }
}
