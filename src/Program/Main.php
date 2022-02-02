<?php

namespace Program;

use \BadMethodCallException;
use Data\String\BackgroundColors;
use Data\String\ColoredString;
use Data\String\ForegroundColors;
use IO\Console;
use Application\Application;
use \PharException;
use \UnexpectedValueException;

class Main
{
    private array $args;
    
    public function __construct(array $args)
    {
        $this->args = $args;
        //Console::Write("*** " . Application::GetName() . " v" . Application::GetVersion() . " by " . Application::GetAuthor() . " ***");
        Console::Write(ColoredString::Get("oOoOoOo ", ForegroundColors::DARK_GRAY, BackgroundColors::MAGENTA) . ColoredString::Get(Application::GetName() . " v" . Application::GetVersion() . " by " . Application::GetAuthor(), ForegroundColors::GREEN, BackgroundColors::MAGENTA) . ColoredString::Get(" oOoOoOo", ForegroundColors::DARK_GRAY, BackgroundColors::MAGENTA));
        $this->start();
    }
    
    private function randomWord($length = 6, $chars = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM") : string
    {
        $code = "";
        
        $clen = strlen($chars) - 1;  
        while (strlen($code) < $length)
        {
            $code .= $chars[mt_rand(0, $clen)];
        }
        
        return $code;
    }
    
    private function debug($text) : void
    {
        $enabled = (isset($this->args[3]) ? $this->args[3] : false);
        $enabled = (boolean) (int) $enabled;
        if ($enabled)
        {
            Console::Write(date("[d.m.Y H:i:s] ", time()) . $text);
        }
    }
    
    private function getDir() : string
    {
        return Application::GetExecutableDirectory();
    }
    
    private function removeDir($dir) : bool
    {
        if (!file_exists($dir) || !is_dir($dir))
        {
            return false;
        }
        if (!is_dir($dir))
        {
            return false;
        }
        foreach (glob($dir . "*", GLOB_MARK) as $filename)
        {
            if (is_dir($filename))
            {
                $this->removeDir($filename);
            }
            else
            {
                $this->debug("Deleting file '" . $filename . "'\n");
                unlink($filename);
            }
        }
        if (file_exists($dir) && is_dir($dir))
        { 
            $this->debug("Deleting folder '" . $dir . "'\n");
            rmdir($dir);
        }
        return true;
    }

    private function White(string $str) : void
    {
        Console::Write(ColoredString::Get($str, ForegroundColors::WHITE));
    }

    private function WhiteLine(string $str) : void
    {
        Console::WriteLine(ColoredString::Get($str, ForegroundColors::WHITE));
    }

    private function GreenLine(string $str) : void
    {
        Console::WriteLine(ColoredString::Get($str, ForegroundColors::GREEN));
    }

    private function RedLine(string $str) : void
    {
        Console::WriteLine(ColoredString::Get($str, ForegroundColors::RED));
    }

    private function YellowLine(string $str) : void
    {
        Console::WriteLine(ColoredString::Get($str, ForegroundColors::YELLOW));
    }
    
    private function start() : void
    {
        Console::WriteLine("\n");
        
        $this->White("Path to zip: ");
        if (!isset($this->args[1]) || $this->args[1] == "null")
        {
            $path_to_zip = Console::ReadLine();
            if (empty($path_to_zip))
            {
                $this->RedLine("Put the path to ZIP-file!");
                unset($this->args[1]);
                $this->start();
                exit;
            }
        }
        else
        {
            $path_to_zip = str_replace("%20", " ", $this->args[1]);
            $this->WhiteLine($path_to_zip);
        }
        
        if (!file_exists($path_to_zip))
        {
            $this->RedLine("No ZIP-file '" . $path_to_zip . "' found!");
            unset($this->args[1]);
            $this->start();
            exit;
        }
        
        if (is_dir($path_to_zip))
        {
            $this->RedLine("'" . $path_to_zip . "' is a folder!");
            unset($this->args[1]);
            $this->start();
            exit;
        }

        $this->White("Path to new PHAR: ");
        if (!isset($this->args[2]) || $this->args[2] == "null")
        {
            $path_to_phar = Console::ReadLine();
            if (empty($path_to_phar))
            {
                $this->RedLine("Put the path to PHAR");
                unset($this->args[1]);
                $this->start();
                exit;
            }
        }
        else
        {
            $path_to_phar = str_replace("%20", " ", $this->args[2]);
            $this->WhiteLine($path_to_phar);
        }
        
        $this->debug("Creating a zip-object.\n");
        $zip = new \ZipArchive();
        $this->debug("Trying to open zip '" . $path_to_zip . "'\n");
        if ($zip->open($path_to_zip) != true)
        {
            $this->RedLine("Failed to open ZIP-file.\n");
            unset($this->args[1]);
            $this->start();
            exit;
        }
        
        $randomFolderName = strtolower($this->randomWord(16));
        $pathToTempFolder = $this->getDir() . $randomFolderName;
        
        while (file_exists($pathToTempFolder))
        {
            $randomFolderName = strtolower($this->randomWord(16));
            $pathToTempFolder = $this->getDir().$randomFolderName;
        }
        
        $this->debug("Trying to create a temp folder: " . $pathToTempFolder . "\n");
        if (@mkdir($pathToTempFolder) != true)
        {
            $this->RedLine("Failed to create temp folder");
            $zip->close();
            $this->start();
            exit;
        }
        $this->debug("Trying to extract files...\n");
        $this->WhiteLine("Converting to phar...");
        if ($zip->extractTo($pathToTempFolder) != true)
        {
            $this->RedLine("Failed to extract to temp folder.");
            @rmdir($pathToTempFolder);
            $zip->close();
            $this->start();
            exit;
        }
        
        $this->debug("Trying to open phar '" . $path_to_phar . "'.");
        try
        {
            $phar = new \Phar($path_to_phar);
        }
        catch (UnexpectedValueException $e)
        {
            $this->RedLine("Failed to open or create '" . $path_to_phar . "'.");
            unset($this->args[2]);
            @rmdir($pathToTempFolder);
            $zip->close();
            $this->start();
            exit;
        }
        $phar->setSignatureAlgorithm(\Phar::SHA512);
        $phar->startBuffering();
        try
        {
            $phar->buildFromDirectory($pathToTempFolder);
        }
        catch (PharException $e)
        {
            $this->RedLine("Failed to convert (PharException).");
            unset($this->args[2]);
            @rmdir($pathToTempFolder);
            $zip->close();
            $this->start();
            exit;
        }
        catch (BadMethodCallException $e)
        {
            $this->RedLine("Failed to convert (BadMethodCallException).");
            unset($this->args[2]);
            @rmdir($pathToTempFolder);
            $zip->close();
            $this->start();
            exit;
        }
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        if (file_exists($pathToTempFolder . DIRECTORY_SEPARATOR . "autoload.php"))
        {
            $this->YellowLine("File 'autoload.php' was found! This file will be executed automatically on PHAR run.");
            $phar->setStub("<?php Phar::mapPhar(); include 'phar://'.__FILE__.'/autoload.php'; __HALT_COMPILER();");
        }
        else
        {
            $phar->setStub("<?php \$compiled = ".time()."; echo \"No bootloader file found. This application has been compiled at \".date('d.m.Y H:i:s', \$compiled).\" (\$compiled).\\n\"; __HALT_COMPILER();");
        }
        $this->removeDir($pathToTempFolder);
        unset($phar);
        $zip->close();
        $this->GreenLine("Done! Press ENTER to close.");
        Console::ReadLine();
        exit;
    }
}