<?php

require __DIR__ . '/../vendor/autoload.php';
use maesierra\Japo\AppContext\JapoAppContext;

$context = JapoAppContext::get();
$context->authManager->isAuthenticated(function() use($context) {
    header("Content-type: application/json");
    echo json_encode(array_map(function($c) {
            /** @var \maesierra\Japo\Entity\KanjiCatalog $c */
            return [
                "id" => $c->getId(),
                "name" => $c->getName(),
                "slug" => $c->getSlug()
            ];
        }, $context->kanjiRepository->listCatalogs())
    );
});#