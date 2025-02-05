<?php
// Set a default value in case $activeTab isn't set.
if (!isset($activeTab)) {
    $activeTab = '';
}
?>

<nav class="flex border-b border-gray-300 mb-6">
    <!-- Portfolio Tab -->
    <a href="<?= $GLOBALS['BASE'] ?>/portfolio/list"
       class="px-4 py-2 border-b-2 <?= $activeTab === 'portfolio' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Portfolio
    </a>
    <!-- Transactions Tab -->
    <a href="<?= $GLOBALS['BASE'] ?>/transaction/list"
       class="px-4 py-2 border-b-2 <?= $activeTab === 'transaction' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Transactions
    </a>
    <!-- Exchangese Tab 1 -->
    <a href="<?= $GLOBALS['BASE'] ?>/exchange/list"
       class="px-4 py-2 border-b-2 <?= $activeTab === 'exchanges' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Exchanges
    </a>
    <!-- Future Tab 2 -->
    <a href="#"
       class="px-4 py-2 border-b-2 <?= $activeTab === 'future2' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
        Future Tab 2
    </a>
</nav>
