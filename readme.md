add to composer.json's repositories:<br>
{
"type": "vcs",
"url": "git@github.com:Astaross/magento-order.git"
}<br>

php -d memory_limit=-1 /usr/local/bin/composer require astaross/magento-order

check for module status:<br>
bin/magento module:status Thunderstone_Order

if disabled:<br>
bin/magento module:enable Thunderstone_Order

bin/magento setup:upgrade<br>
bin/magento setup:di:compile<br>
bin/magento c:c