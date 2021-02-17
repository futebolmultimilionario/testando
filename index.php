<?php
include_once __DIR__ . '/../vendor/autoload.php';

$parser = new aymanrb\UnstructuredTextParser\TextParser('vendor\aymanrb\php-unstructured-text-parser\examples\templates');

$textToParse = preg_replace('/^[ \t]*[\r\n]+/m', '', strtolower(file_get_contents('vendor\aymanrb\php-unstructured-text-parser\examples\test_txt_files\m_0.txt')));

//performs brute force parsing against all available templates, returns first match successful parsing
$parseResults = $parser->parseText($textToParse);
print_r($parseResults->getParsedRawData());
echo $textToParse."<br><br>";
//slower, performs a similarity check on available templates to select the most matching template before parsing
print_r(
    $parser
        ->parseText($textToParse, true)
        ->getParsedRawData()
);
?>
