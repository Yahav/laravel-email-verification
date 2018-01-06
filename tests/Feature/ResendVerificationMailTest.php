<?php
/**
 * Created by Josias Montag
 * Date: 06.01.18 15:42
 * Mail: josias@montag.info
 */

namespace Lunaweb\EmailVerification\Tests\Feature;



use Illuminate\Support\Facades\Notification;
use Lunaweb\EmailVerification\Notifications\EmailVerification;

class ResendVerificationMailTest extends TestCase
{




    public function setUp()
    {
        parent::setUp();


        Notification::fake();


    }


    public function testResend() {

        $this->withoutExceptionHandling();


        $user =  User::create(['email' => 'test@user.com', 'verified' => false]);

        $response = $this->actingAs($user)->json('POST', '/register/verify/resend', [
            'email' => 'new@email.info',
        ]);
        $response->assertRedirect('/home');


        Notification::assertSentTo(
            [$user], EmailVerification::class
        );

        $this->assertFalse((boolean)$user->verified);

        $notification = Notification::sent($user, EmailVerification::class)->first();

        $activationUrl = $notification->toMail($user)->actionUrl;

        $response = $this->get($activationUrl);

        $response->assertRedirect('/home');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue((boolean)$user->verified);
        $this->assertTrue($user->email === 'new@email.info');


    }



}