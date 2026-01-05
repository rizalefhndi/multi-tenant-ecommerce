<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantLoginToken;
use Illuminate\Http\Request;

class SSOController extends Controller
{
    /**
     * Generate SSO token and redirect to tenant
     */
    public function redirect(Request $request, string $tenantId)
    {
        $user = auth()->user();
        
        // Debug: Check if user is authenticated
        if (!$user) {
            \Log::error('SSO Redirect: User not authenticated');
            return redirect()->route('login')->with('error', 'Please login first.');
        }
        
        \Log::info('SSO Redirect Debug', [
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);
        
        // Verify user owns this tenant
        $tenant = Tenant::where('id', $tenantId)
            ->where('owner_id', $user->id)
            ->first();
        
        \Log::info('SSO Tenant Lookup', [
            'tenant_found' => $tenant ? true : false,
            'tenant_id_searched' => $tenantId,
        ]);
            
        if (!$tenant) {
            \Log::error('SSO: Tenant not found or not owned by user', [
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
            ]);
            abort(403, 'You do not have access to this store.');
        }

        // Get tenant domain
        $domain = $tenant->domains()->first();
        
        if (!$domain) {
            abort(404, 'Store domain not found.');
        }

        // Generate login token
        $loginToken = TenantLoginToken::generateForUser($user, $tenantId);

        // Redirect to tenant SSO endpoint
        $ssoUrl = 'http://' . $domain->domain . ':8000/sso?token=' . $loginToken->token;
        
        return redirect()->away($ssoUrl);
    }
}
