<?php

$assignedId = mb_substr('{{Ответственный}}', 5);

switch ($assignedId) {
    case 8:
        $purchasingManager = 525;
        break;
    case 325:
        $purchasingManager = 525;
        break;
    case 321:
        $purchasingManager = 1075;
        break;
    case 273:
        $purchasingManager = 1075;
        break;
    case 260:
        $purchasingManager = 309;
        break;
    case 313:
        $purchasingManager = 309;
        break;
    case 255:
        $purchasingManager = 309;
        break;
    case 46:
        $purchasingManager = 525;
        break;
    case 327:
        $purchasingManager = 1099;
        break;
    case 1095:
        $purchasingManager = 1099;
        break;
    case 1105:
        $purchasingManager = 1099;
        break;
    case 1107:
        $purchasingManager = 1075;
        break;
    case 1111:
        $purchasingManager = 1075;
        break;

    default:
        $purchasingManager = '';
        break;
}

switch ($assignedId) {
    case 325:
        $assistant = 1088;
        break;
    case 8:
        $assistant = 42;
        break;
    case 46:
        $assistant = 42;
        break;
    case 321:
        $assistant = 42;
        break;
    case 273:
        $assistant = 338;
        break;
    case 327:
        $assistant = 338;
        break;
    case 1095:
        $assistant = 42;
        break;
    case 1105:
        $assistant = 1101;
        break;
    case 1107:
        $assistant = 1101;
        break;
    case 1111:
        $assistant = 338;
        break;

    default:
        $assistant = '';
        break;
}

switch ($assignedId) {
    case 8:
        $productManager = 486;
        break;
    case 46:
        $productManager = 197;
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
    case 1095:
        $productManager = 23;
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
