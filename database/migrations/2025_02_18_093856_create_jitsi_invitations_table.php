<?php

use App\Models\JitsiMeeting;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJitsiInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jitsi_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(JitsiMeeting::class);
            $table->foreignIdFor(User::class);
            $table->string('jitsi_auth_id')->unique();
            $table->boolean('is_moderator')->default(false);
            $table->timestamps();

            $table->unique(['jitsi_meeting_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jitsi_invitations');
    }
}
