<?php


namespace Expresso\Context\Type;


class Basic extends TypeAbstract
{

    public function prepare()
    {
        $configuration =
            array(
                'project' =>
                    array(
                        'default_stage' => 'production',
                        'keep_release' => 3,
                        'unwanted_file' => null,
                        'unwanted_folder' => null,
                        'permission' => array(
                            'user_group' => null,
                            'use_sudo' => false,
                            'release_writable' => null,
                            'shared_writable' => null,
                        ),
                        'shared' => array(
                            'file' => null,
                            'folder' => null,
                        )
                    ),
                'tasks' => array(
                    'before' => null,
                    'after' => null,
                    'list' => array(
                        'deploy' => '\Expresso\Task\Context\Deploy\Basic',
                        'rollback' => '\Expresso\Task\Rollback',
                        'deploy:setup' => '\Expresso\Task\Deploy\Setup',
                        'deploy:package:prepare' => '\Expresso\Task\Deploy\Package\Prepare',
                        'deploy:package:unwanted:file' => '\Expresso\Task\Deploy\Package\UnwantedFile',
                        'deploy:package:unwanted:folder' => '\Expresso\Task\Deploy\Package\UnwantedFolder',
                        'deploy:package:compress' => '\Expresso\Task\Deploy\Package\Compress',
                        'deploy:release:create' => '\Expresso\Task\Deploy\Release\Create',
                        'deploy:release:upload' => '\Expresso\Task\Deploy\Release\Upload',
                        'deploy:release:extract' => '\Expresso\Task\Deploy\Release\Extract',
                        'deploy:dependencies:composer' => '\Expresso\Task\Deploy\Dependencies\Composer',
                        'deploy:shared:folder' => '\Expresso\Task\Deploy\Shared\Folder',
                        'deploy:shared:file' => '\Expresso\Task\Deploy\Shared\File',
                        'deploy:permission:release' => '\Expresso\Task\Deploy\Permission\Release',
                        'deploy:permission:shared' => '\Expresso\Task\Deploy\Permission\Shared',
                        'deploy:symlink' => '\Expresso\Task\Deploy\Symlink',
                        'deploy:clean' => '\Expresso\Task\Deploy\Clean',
                    ),
                ),
                'servers' => array(
                    'prod.a' => array(
                        'host' => 'a.production.com',
                        'user' => 'foo',
                        'password' => 'bar',
                        'deploy_dir' => '/path/to/deploy',
                        'roles' => array(
                            'web',
                            'db',
                        ),
                    ),
                    'prod.b' => array(
                        'host' => 'b.production.com',
                        'user' => 'foo',
                        'password' => 'bar',
                        'deploy_dir' => '/path/to/deploy',
                        'roles' => array(
                            'web',
                        ),
                    )
                ),
                'stages' => array(
                    'production' => array(
                        'prod.a',
                        'prod.b',
                    )
                )
            );

        $this->configuration = array_merge($configuration, $this->data);
    }
}
