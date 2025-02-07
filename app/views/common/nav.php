<?php
// Set a default value in case $activeTab isn't set.
if (!isset($activeTab)) {
    $activeTab = '';
}
?>

<nav class="flex border-b border-gray-300 mb-6">

    <a href="<?= $GLOBALS['BASE'] ?>/portfolio/list"
       class="px-4 py-2 border-b-2 <?= $activeTab === 'portfolio' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Portfolio
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
</nav>
