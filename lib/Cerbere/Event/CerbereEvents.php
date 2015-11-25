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

namespace Cerbere\Event;

/**
 * Class CerbereEvents
 * @package Cerbere\Event
 */
final class CerbereEvents
{
    const CERBERE_FILE_DISCOVERED = 'cerbere.file_discovered';

    const CERBERE_PRE_ACTION = 'cerbere.action.pre';

    const CERBERE_POST_ACTION = 'cerbere.action.post';

    const CERBERE_DO_ACTION = 'cerbere.action.do';

    const CERBERE_REPORT_ACTION = 'cerbere.action.report';

    const CERBERE_DONE_ACTION = 'cerbere.action.done';
}
