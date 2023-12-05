<?php
// Трейт для текстових даних
trait TextDataTrait {
    public function processData($data) {
        echo "Processing text data: $data\n";
    }
}

// Трейт для числових даних
trait NumericDataTrait {
    public function processData($data) {
        echo "Processing numeric data: $data\n";
    }
}

// Клас, який використовує обидва трейти
class DataProcessor {
    use TextDataTrait, NumericDataTrait {
        TextDataTrait::processData insteadof NumericDataTrait; // Використовувати метод processData з TextDataTrait, а не NumericDataTrait
        NumericDataTrait::processData as processNumericData; // Псевдонім для методу processData з NumericDataTrait
    }
}

// Створення екземпляру класу
$dataProcessor = new DataProcessor();

// Виклик методу для текстових даних
$dataProcessor->processData('Hello, world!'); // Виведе "Processing text data: Hello, world!"

// Виклик методу для числових даних через псевдонім
$dataProcessor->processNumericData(42); // Виведе "Processing numeric data: 42"