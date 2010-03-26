<?php
class Sample_Services_Content {
    
    public function __construct()
    {
        $this->_filename = APPLICATION_PATH . '/data/content.txt';
    }
    
	/**
	 * Return the value of the content file
	 *
	 * @return string
	 */
	public function read()
	{
		return file_get_contents($this->_filename);
	}
	
	/**
	 * Writes a string to the content file
	 * 
	 * @param $content string
	 * @return boolean
	 */
	public function write($content)
	{
	    return file_put_contents($this->_filename, $content);
	}
}