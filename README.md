# Database config

````php
<?php
class DATABASE_CONFIG {
        public $default = array(
                'datasource'    => 'Mongodb.MongodbSource',
                'host'                  => 'mongo',
                'database'              => 'phpgraph',
                'port'                  => 27017,
                'prefix'                => '',
                'persistent'    => true,
        );

        public $platform = array(
                'datasource'    => 'Rest.RestSource',
                'host'                  => 'https://platform-cw.officecloud.dk',
                'format'                => 'json',
                'encoding'              => 'utf-8',
        );

        public $graph = array(
                'datasource'    => 'Rest.RestSource',
                'host'                  => 'https://graph.officecloud.dk',
                'format'                => 'json',
                'encoding'              => 'utf-8',
        );
}
?>
````