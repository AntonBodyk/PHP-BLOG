<?php

trait TextDataTrait {
    public function processData($data) {
        echo "Processing text data: $data\n";
    }
}

trait NumericDataTrait {
    public function processData($data) {
        echo "Processing numeric data: $data\n";
    }
}

class DataProcessor {
    use TextDataTrait, NumericDataTrait {
        TextDataTrait::processData insteadof NumericDataTrait;
        NumericDataTrait::processData as processNumericData;
    }
}


$dataProcessor = new DataProcessor();

$dataProcessor->processData('Hello, world!');

$dataProcessor->processNumericData(42);