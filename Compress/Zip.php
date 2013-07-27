<?php

namespace LessPHP\Compress;


class Zip
{
    /**
     * The underlying ZipArchive instance that does the heavy lifting.
     *
     * @var ZipArchive
     */
    protected $_zip;
  
    /**
     * Constructor for a new archiver instance.
     *
     * @param $file
     *   The full system file of the archive to manipulate.  Only local files
     *   are supported.  If the file does not yet exist, it will be created if
     *   appropriate.
     */
    //public function __construct($file)
    public function __construct($file)
    {
        $this->_zip = new \ZipArchive();
        if ($this->_zip->open($file, \ZIPARCHIVE::CREATE) !== TRUE) {
            // @todo: This should be an interface-specific exception some day.
            throw new \Exception("Cannot open $file");
        }
    }

    /**
     * Add the specified file or directory to the archive.
     *
     * @param $file
     *   The full system path of the file or directory to add. Only local files
     *   and directories are supported.
     * @return ArchiveInterface
     *   The called object.
     */
    public function add($file, $targetfile = NULL)
    {
        $this->_zip->addFile($file, $targetfile);

        return $this;
    }

    /**
     * Remove the specified file from the archive.
     *
     * @param $file
     *   The file name relative to the root of the archive to remove.
     * @return ArchiveInterface
     *   The called object.
     */
    public function remove($file)
    {
        $this->_zip->deleteName($file);

        return $this;
    }

    /**
     * Extract multiple files in the archive to the specified path.
     *
     * @param $path
     *   A full system path of the directory to which to extract files.
     * @param $files
     *   Optionally specify a list of files to be extracted. Files are
     *   relative to the root of the archive. If not specified, all files
     *   in the archive will be extracted
     * @return ArchiveInterface
     *   The called object.
     */
    public function extract($path, Array $files = array())
    {
        if ($files) {
            $this->_zip->extractTo($path, $files);
        } else {
            $this->_zip->extractTo($path);
        }

        return $this;
    }

    /**
     * List all files in the archive.
     *
     * @return
     *   An array of file names relative to the root of the archive.
     */
    public function listContents()
    {
        $files = array();
        for ($i = 0; $i < $this->_zip->numFiles; $i++) {
            $files[] = $this->_zip->getNameIndex($i);
        }
        return $files;
    }
    
    public function getFromName($file)
    {
        return $this->_zip->getFromName($file);
    }
    
    public function close()
    {
        $this->_zip->close();
    }
    
    /**
     * Retrieve the zip engine itself.
     *
     * In some cases it may be necessary to directly access the underlying
     * ZipArchive object for implementation-specific logic. This is for advanced
     * use only as it is not shared by other implementations of ArchiveInterface.
     *
     * @return
     *   The ZipArchive object used by this object.
     */
    public function getArchive()
    {
        return $this->_zip;
    }
}
