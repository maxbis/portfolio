<?php
// Set a default value in case $activeTab isn't set.
if (!isset($activeTab)) {
    $activeTab = $model;
}

?>

<nav class="flex border-b border-gray-300 mb-6">


    <a href="<?= $GLOBALS['BASE'] ?>/portfolio/lista"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'portfolio' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Portfolio
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/portfolio/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'portfolios' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Portfolios
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/transaction/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'transaction' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Transactions
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/exchange/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'exchanges' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Exchanges
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/broker/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'brokers' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Brokers
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/strategy/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'strategies' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Strategies
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/quote/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'quote' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Quotes
    </a>

    <a href="<?= $GLOBALS['BASE'] ?>/symbol/list"
        class="px-4 py-2 border-b-2 <?= $activeTab === 'symbol' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Instruments
    </a>

</nav>