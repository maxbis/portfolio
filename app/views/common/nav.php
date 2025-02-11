<p?php // Set a default value in case $activeTab isn't set. if (!isset($activeTab)) { $activeTab=$model; } ?>

    <nav class="flex border-b border-gray-300 mb-6 space-x-4">
        <!-- Portfolio with Dropdown Submenu -->
        <div class="relative group inline-flex items-center">
            <a href="<?= $GLOBALS['BASE'] ?>/portfolio/lista"
                class="px-4 py-2 border-b-2 <?= $activeTab === 'portfolio' ? 'text-blue-600 border-blue-600 font-medium' : 'text-gray-600 border-transparent hover:text-blue-600 hover:border-blue-600' ?>">
                Portfolio
            </a>
            <!-- Dropdown menu -->
            <div
                class="absolute left-0 top-6 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg opacity-0 group-hover:opacity-100 transform scale-95 group-hover:scale-100 transition duration-150 ease-in-out pointer-events-none group-hover:pointer-events-auto z-10">
                <a href="<?= $GLOBALS['BASE'] ?>/portfolio/lista/symbol"
                    class="block text-sm px-4 py-2 text-gray-700 hover:bg-gray-100">By Symbol</a>
                <a href="<?= $GLOBALS['BASE'] ?>/portfolio/lista/broker"
                    class="block text-sm px-4 py-2 text-gray-700 hover:bg-gray-100">By Broker</a>
                <a href="<?= $GLOBALS['BASE'] ?>/portfolio/lista/strategy"
                    class="block text-sm px-4 py-2 text-gray-700 hover:bg-gray-100">By Strategy</a>
                <p class="block text-sm px-4 py-2 text-gray-700">-----</p>
                <a href="<?= $GLOBALS['BASE'] ?>/portfolio/list"
                    class="block text-sm px-4 py-2 text-gray-700 hover:bg-gray-100">Detailed</a>
            </div>
        </div>

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