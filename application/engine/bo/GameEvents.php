<?php /*
    Copyright 2015-2017 Cédric Levieux, Parti Pirate

    This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/

class GameEvents {

    const CREATE_MEETING    = "0a730dcc-3a64-11e7-bc38-0242ac110005";
    const CREATE_AGENDA     = "0a732379-3a64-11e7-bc38-0242ac110005";
    const CREATE_MOTION     = "0a732471-3a64-11e7-bc38-0242ac110005";
    const HAS_VOTED         = "0a73250f-3a64-11e7-bc38-0242ac110005";
    const HAS_CHATED        = "0a732593-3a64-11e7-bc38-0242ac110005";
    const HAS_APPROVED      = "3ba26455-4dc1-11e7-bc38-0242ac110005";
    const HAS_REPROVED      = "3ba2669c-4dc1-11e7-bc38-0242ac110005";
    const IS_APPROVED       = "3ba267d1-4dc1-11e7-bc38-0242ac110005";
    const IS_REPROVED       = "3ba26901-4dc1-11e7-bc38-0242ac110005";
}