<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\deck_repository;

/**
 * Unit tests for study deck visibility rules.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \local_chemillusion\cards\deck_repository
 */
final class deck_repository_test extends \advanced_testcase {
    public function test_private_deck_is_visible_to_owner_only_unless_manager(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $deckid = deck_repository::create_deck($owner->id, null, 'Private deck', 'private');
        $deck = deck_repository::get_deck($deckid);

        $this->assertTrue(deck_repository::can_view_deck($deck, $owner->id));
        $this->assertFalse(deck_repository::can_view_deck($deck, $other->id));
        $this->assertTrue(deck_repository::can_view_deck($deck, $other->id, null, true));
    }

    public function test_course_deck_requires_matching_course_scope(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $othercourse = $this->getDataGenerator()->create_course();
        $deckid = deck_repository::create_deck($owner->id, $course->id, 'Course deck', 'course');
        $deck = deck_repository::get_deck($deckid);

        $this->assertTrue(deck_repository::can_view_deck($deck, $owner->id));
        $this->assertTrue(deck_repository::can_view_deck($deck, 0, $course->id));
        $this->assertFalse(deck_repository::can_view_deck($deck, 0, $othercourse->id));
        $this->assertFalse(deck_repository::can_view_deck($deck, 0));
    }

    public function test_list_decks_without_course_does_not_leak_course_decks(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $viewer = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        deck_repository::create_deck($owner->id, $course->id, 'Course deck', 'course');
        deck_repository::create_deck($owner->id, null, 'Site deck', 'site');

        $names = array_map(static function ($deck) {
            return $deck->name;
        }, deck_repository::list_decks($viewer->id));

        $this->assertContains('Site deck', $names);
        $this->assertNotContains('Course deck', $names);
    }

    public function test_unknown_visibility_is_stored_as_private(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $deckid = deck_repository::create_deck($owner->id, null, 'Deck', 'public');
        $deck = deck_repository::get_deck($deckid);

        $this->assertSame('private', $deck->visibility);
    }
}
