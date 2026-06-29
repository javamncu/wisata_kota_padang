<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provider (swappable)
    |--------------------------------------------------------------------------
    | Which LLM provider drives the AI Concierge. Only the active provider's
    | block below is used, so swapping is a single .env change.
    */
    'provider' => env('CONCIERGE_PROVIDER', 'gemini'),

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 20),
        'retries' => (int) env('GEMINI_RETRIES', 2), // extra attempts on transient 5xx
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    | Used if the live ListModels API call fails. Admin picks the active model
    | from the UI (stored in settings), falling back to gemini.model above.
    */
    'models_fallback' => [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-2.0-flash',
        'gemini-2.0-flash-lite',
    ],

    /*
    | Estimated free-tier daily request caps per model (Requests Per Day).
    | These are ESTIMATES for the "sisa" hint — Google does not expose real
    | remaining quota via API. Adjust to match your plan.
    */
    'model_daily_caps' => [
        'gemini-2.5-flash' => 250,
        'gemini-2.5-flash-lite' => 1000,
        'gemini-2.0-flash' => 200,
        'gemini-2.0-flash-lite' => 200,
        'gemini-1.5-flash' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Recommendations
    |--------------------------------------------------------------------------
    */
    'results' => (int) env('CONCIERGE_RESULTS', 6),

    /*
    |--------------------------------------------------------------------------
    | Rate limits (messages per day) — cost control
    |--------------------------------------------------------------------------
    | Admins are never limited. Logged-in users get a looser cap than guests.
    */
    'rate_limits' => [
        'guest' => (int) env('CONCIERGE_LIMIT_GUEST', 8),
        'user' => (int) env('CONCIERGE_LIMIT_USER', 40),
        'decay_seconds' => (int) env('CONCIERGE_LIMIT_DECAY', 86400),
    ],
];
