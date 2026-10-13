<?php

namespace Tests\Feature\Personal;

use App\Models\Attendance;
use App\Models\Memo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MemoAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_edit_search_and_delete_memos(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Volt::test('memos.index')
            ->call('create')
            ->set('title', '장보기')
            ->set('content', '우유, 계란')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('장보기');

        $memo = Memo::sole();
        $this->assertSame($user->id, $memo->user_id);

        $component->call('edit', $memo->id)->set('title', '장보기 목록')->call('save');
        $this->assertSame('장보기 목록', $memo->refresh()->title);

        $component->set('search', '계란')->assertSee('장보기 목록');
        $component->set('search', '없는말')->assertDontSee('장보기 목록');

        $component->call('delete', $memo->id);
        $this->assertModelMissing($memo);
    }

    public function test_memos_of_other_users_are_invisible(): void
    {
        $memo = Memo::factory()->create(['title' => '남의 메모']);
        $this->actingAs(User::factory()->create());

        Volt::test('memos.index')->assertDontSee('남의 메모');
        Volt::test('memos.index')->call('edit', $memo->id)->assertNotFound();
        Volt::test('memos.index')->call('delete', $memo->id)->assertNotFound();

        $this->assertModelExists($memo);
    }

    public function test_user_can_check_in_once_a_day(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Volt::test('attendance')->call('check')->call('check')->assertSee('오늘 출석을 완료했습니다.');

        $this->assertSame(1, Attendance::count());
        $this->assertSame(today()->toDateString(), Attendance::sole()->attended_on->toDateString());
    }

    public function test_streak_counts_consecutive_days(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $user = User::factory()->create();

        foreach (['2026-10-10', '2026-10-11', '2026-10-12', '2026-10-08'] as $date) {
            $user->attendances()->create(['attended_on' => $date]);
        }

        $this->actingAs($user);

        // Not checked in yet today: the streak up to yesterday still counts.
        Volt::test('attendance')->assertViewHas('streak', 3)->assertViewHas('total', 4);

        Volt::test('attendance')->call('check')->assertViewHas('streak', 4);
    }

    public function test_month_navigation_ignores_invalid_or_future_months(): void
    {
        Carbon::setTestNow('2026-10-13 10:00:00');
        $this->actingAs(User::factory()->create());

        Volt::test('attendance')->set('month', '2026-09')->assertSee('2026.09');
        Volt::test('attendance')->set('month', '2027-01')->assertSee('2026.10');
        Volt::test('attendance')->set('month', 'bogus')->assertSee('2026.10');
    }
}
