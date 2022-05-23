<?php

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

$connector = new FilePrintConnector('/dev/usb/lp0');
$printer = new Printer($connector);
function intLowHigh(int $input, int $length)
{
    $maxInput = (256 << ($length * 8) - 1);
    $outp = "";
    for ($i = 0; $i < $length; $i++) {
        $outp .= chr($input % 256);
        $input = (int)($input / 256);
    }
    return $outp;
}
function dataHeader(array $inputs, bool $long = true)
{
    $outp = [];
    foreach ($inputs as $input) {
        if ($long) {
            $outp[] = intLowHigh($input, 2);
        } else {
            $outp[] = chr($input);
        }
    }
    return implode("", $outp);
}

$printer->setLineSpacing(18);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setTextSize(2, 2);

$bonkers = "\x42\x4F\x4E\x4B\x45\x52\x53";
$cafe = "\x20\x43\x41\x46\x45\x20\x20";
$connector->write("\xC9\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xBB\x0a");
$connector->write("\xBA\x20\x20".$bonkers."\x20\x20\xBA\x0A");
$connector->write("\xBA\x20\x20".$cafe."\x20\x20\xBA\x0A");

$connector->write("\xBA\x20\x20\x20");
$printer->setTextSize(1, 1);
$printer->text("Thank you ");
$printer->setTextSize(2, 2);
$connector->write("\x20\x20\x20\xBA\x0A");
$connector->write("\xC8\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xCD\xBC\x0a");


$printer->feed(10);
$printer->text('ORDER 10001');
$printer->feed(20);
$printer->setTextSize(1, 1);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text('Big Burger               x 1');
$printer->feed(5);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text('Chips                    x 1');
$printer->feed(25);
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text('Table 001');
$printer->feed(10);
$printer->cut();
//$connector->write("\x1B\x28\x41\x00\x03\x61\x01\x01\x00");
$printer->close();
