add:<br>
{
"type": "vcs",
"url": "git@github.com:Astaross/magento-order.git"
}<br> to composer.json's repositories

php -d memory_limit=1 /usr/local/bin/composer require astaross/magento-order

check for module status:<br>
bin/magento module:status Thunderstone_Order

if disabled:
bin/magento module:enable Thunderstone_Order$

bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento c:c