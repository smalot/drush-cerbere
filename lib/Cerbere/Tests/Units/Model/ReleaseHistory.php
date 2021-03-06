<?php

/**
 * Drush Cerbere command line tools.
 * Copyright (C) 2015 - Sebastien Malot <sebastien@malot.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Cerbere\Tests\Units\Model;

use Cerbere\Model\Release;
use Cerbere\Tests\AbstractTest;

/**
 * Class ReleaseHistory
 * @package Cerbere\Tests\Units\Model
 */
class ReleaseHistory extends AbstractTest
{
    public function testCodeCoverage()
    {
        $release_history = new \Cerbere\Model\ReleaseHistory();

        // 5.8 should not be safe.
        $project = $this->createProjectFromFile('5.8');

        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-5.8');

        $release_history->prepare($project);
        $release_history->compare($project);

        $this->integer($project->getStatus())->isEqualTo(\Cerbere\Model\ReleaseHistory::UPDATE_NOT_SECURE);

        // 5.10 should be outdated.
        $project = $this->createProjectFromFile('5.10');

        $this->string($project->getCore())->isEqualTo('7.x');
        $this->string($project->getVersion())->isEqualTo('7.x-5.10');

        $release_history->prepare($project);
        $release_history->compare($project);

        $this->integer($project->getStatus())->isEqualTo(\Cerbere\Model\ReleaseHistory::UPDATE_NOT_CURRENT);

        /********************************************************/

        // Test with a DEV release.
        $release_history = new \Cerbere\Model\ReleaseHistory();

        $project = $this->createProjectSearchAPIFromFile();

        $release_history->prepare($project);
        $release_history->compare($project);

        $this->integer($project->getStatus())->isEqualTo(\Cerbere\Model\ReleaseHistory::UPDATE_NOT_SECURE);
    }

    /**
     * @param string $version
     * @return \Cerbere\Model\Project
     */
    protected function createProjectFromFile($version)
    {
        $data = 'name = Twitter
description = Adds integration with the Twitter microblogging service.
php = 5.1
core = 7.x
files[] = twitter_views_field_handlers.inc
files[] = twitter.lib.php
files[] = tests/core.test
files[] = tests/input_filters.test
dependencies[] = oauth_common
configure = admin/config/services/twitter

; Dependencies that are only used with the tests.
test_dependencies[] = oauth
test_dependencies[] = views

; Information added by Drupal.org packaging script on 2015-10-05
version = "7.x-'.$version.'"
core = "7.x"
project = "twitter"
datestamp = "1444046332"';

        $filename = $this->createFile($data);

        $info = new \Cerbere\Parser\Info();
        $info->processFile($filename);
        $project = $info->getProject();

        return $project;
    }

    protected function createProjectSearchAPIFromFile()
    {
        $data = 'name = Search API
description = "Provides a generic API for modules offering search capabilites."
dependencies[] = entity
core = 7.x
package = Search

files[] = search_api.test
files[] = includes/callback.inc
files[] = includes/callback_add_aggregation.inc
files[] = includes/callback_add_hierarchy.inc
files[] = includes/callback_add_url.inc
files[] = includes/callback_add_viewed_entity.inc
files[] = includes/callback_bundle_filter.inc
files[] = includes/callback_comment_access.inc
files[] = includes/callback_language_control.inc
files[] = includes/callback_node_access.inc
files[] = includes/callback_node_status.inc
files[] = includes/callback_role_filter.inc
files[] = includes/callback_user_status.inc
files[] = includes/datasource.inc
files[] = includes/datasource_entity.inc
files[] = includes/datasource_external.inc
files[] = includes/datasource_multiple.inc
files[] = includes/exception.inc
files[] = includes/index_entity.inc
files[] = includes/processor.inc
files[] = includes/processor_highlight.inc
files[] = includes/processor_html_filter.inc
files[] = includes/processor_ignore_case.inc
files[] = includes/processor_stopwords.inc
files[] = includes/processor_tokenizer.inc
files[] = includes/processor_transliteration.inc
files[] = includes/query.inc
files[] = includes/server_entity.inc
files[] = includes/service.inc

configure = admin/config/search/search_api

; Information added by Drupal.org packaging script on 2016-02-26
version = "7.x-1.16+29-dev"
core = "7.x"
project = "search_api"
datestamp = "1456500713"
';

        $filename = $this->createFile($data);

        $info = new \Cerbere\Parser\Info();
        $info->processFile($filename);
        $project = $info->getProject();

        return $project;
    }
}
