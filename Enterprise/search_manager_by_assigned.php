<?php

$assignedId = mb_substr('{{Ответственный}}', 5);

switch ($assignedId) {
    case 46:
        $purchasingManager = 274;
        break;
    case 8:
        $purchasingManager = 212;
        break;
    case 325:
        $purchasingManager = 212;
        break;
    case 327:
        $purchasingManager = 212;
        break;
    case 273:
        $purchasingManager = 481;
        break;
    case 321:
        $purchasingManager = 481;
        break;

    default:
        $purchasingManager = '';
        break;
}

switch ($assignedId) {
    case 325:
        $assistant = 42;
        break;
    case 8:
        $assistant = 42;
        break;
    case 46:
        $assistant = 42;
        break;
    case 321:
        $assistant = 338;
        break;
    case 273:
        $assistant = 338;
        break;
    case 327:
        $assistant = 338;
        break;

    default:
        $assistant = '';
        break;
}

switch ($assignedId) {
    case 8:
        $productManager = 23;
        break;
    case 46:
        $productManager = 23;
        break;
    case 273:
        $productManager = 486;
        break;
    case 321:
        $productManager = 486;
        break;
    case 325:
        $productManager = 197;
        break;
    case 327:
        $productManager = 197;
        break;

    default:
        $productManager = '';
        break;
}

$purchasingManager = 'user_' . $purchasingManager;
$assistant = 'user_' . $assistant;
$productManager = 'user_' . $productManager;

$this->SetVariable('purchasingManager', $purchasingManager);
$this->SetVariable('assistant', $assistant);
$this->SetVariable('productManager', $productManager);
