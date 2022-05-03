Local quiz additional behaviour
===============================

A local plugin that adds additional behaviour to the quiz activity module.

The additional behaviours are:

1. Hide a users quiz answer that was answered correctly in the immediate previous attempt and shift their previous correct answer to the new attempt.
2. Hide or show the correctly answered question from the immediate previous attempt.
3. Custom grading functionality.

These customisations were pulled from a bunch of core files that were modified in order to achieve this behaviour.

The local plugin is used to override the core quiz and question behaviour classes and renderers so that custom functionality can happen.

The local plugin does not work on its own, it requires a custom theme with a bunch of render overrides.

Todo
====

Add the "render overrides" required and some documentation so that this can be applied to other places that may need it.

License
=======

2022 Skills Consulting Group

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/.