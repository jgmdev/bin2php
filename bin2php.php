#!/usr/bin/env php
<?php

if($argc <= 1)
{
    print <<<HELP
Script to convert a binary file into a php array.
-------------------------------------------------

Usage: bin2php <input_file> <output_file>

HELP;

    exit;
}

if(!isset($argv[2]))
{
    print "Please specify the output file.\n";
    exit(1);
}

$input = fopen($argv[1], "rb");

if(!$input)
{
    print "The file {$argv[1]} does not exists.\n";
    exit(0);
}

$file_parts = explode("/", str_replace("\\", "/", $argv[1]));
$function_name = str_replace(
    array(" ", "-", "."), 
    "_", 
    preg_replace(
        '/[^a-zA-Z0-9 -\.]/', 
        '', 
        $file_parts[count($file_parts)-1]
    )
);

$contents = "<?php
function binphp_$function_name()
{
    \$bytes = array(";

$columns = 0;

while(!feof($input))
{
    $byte = fread($input, 1);
    
    
    if($columns == 0)
        $contents .= "\n        ";
        
    if($columns <= 8)
    {
        $contents .= sprintf("0x%02x, ", ord($byte));
        
        $columns++;
    }
    else
    {
        $contents .= sprintf("0x%02x,", ord($byte));
        
        $columns = 0;
    }
}

$contents = rtrim($contents, " ,");

$contents .= "
    );

    \$output = \"\";
    foreach(\$bytes as \$byte)
    {
        \$output .= chr(\$byte);
    }
    
    return \$output;
}\n
";

fclose($input);

if(!file_put_contents($argv[2], $contents))
{
    print "Could not open output file for writing.\n";
    exit(1);
}


