<?php

return [

    /*
    | The assistant is optional. It stays off until this is true.
    | "false" in .env must not be treated as on.
    | A Gemini key turns it on only when FURSHIELD_ASSISTANT_ENABLED is unset.
    */
    'enabled' => (function (): bool {
        $flag = env('FURSHIELD_ASSISTANT_ENABLED');

        if ($flag === null || $flag === '') {
            return filled(env('GEMINI_API_KEY')) || filled(env('FURSHIELD_ASSISTANT_API_KEY'));
        }

        return filter_var($flag, FILTER_VALIDATE_BOOLEAN);
    })(),

    /*
    | Server only. Never read this from a Blade view, a Vite variable, or a JSON response.
    | GEMINI_API_KEY is used when FURSHIELD_ASSISTANT_API_KEY is empty.
    */
    'api_key' => filled(env('FURSHIELD_ASSISTANT_API_KEY')) ? env('FURSHIELD_ASSISTANT_API_KEY') : env('GEMINI_API_KEY'),

    'base_url' => rtrim((function (): string {
        $gemini = 'https://generativelanguage.googleapis.com/v1beta/openai';
        $openai = 'https://api.openai.com/v1';
        $configured = env('FURSHIELD_ASSISTANT_BASE_URL');
        $usingGemini = ! filled(env('FURSHIELD_ASSISTANT_API_KEY')) && filled(env('GEMINI_API_KEY'));

        if ($usingGemini && ($configured === null || $configured === '' || $configured === $openai)) {
            return $gemini;
        }

        return (string) ($configured ?: $openai);
    })(), '/'),

    'model' => (function (): string {
        $configured = env('FURSHIELD_ASSISTANT_MODEL');
        $usingGemini = ! filled(env('FURSHIELD_ASSISTANT_API_KEY')) && filled(env('GEMINI_API_KEY'));

        if ($usingGemini && ($configured === null || $configured === '' || $configured === 'gpt-4o-mini')) {
            return (string) env('GEMINI_MODEL', 'gemini-2.5-flash');
        }

        return (string) ($configured ?: 'gpt-4o-mini');
    })(),

    'timeout' => (int) env('FURSHIELD_ASSISTANT_TIMEOUT', 20),

    'per_minute' => (int) env('FURSHIELD_ASSISTANT_PER_MINUTE', 12),

    'max_chars' => 800,

    'disclaimer' => 'FurShield Assistant does not diagnose and does not give medication doses. A veterinarian has to examine your pet for anything serious. For trouble breathing, seizures, bleeding, collapse, poisoning, pale gums, a cat that cannot urinate, or a rabbit that is not eating, use the emergency check.',

    'fallback' => "The assistant can't answer right now. Nothing here is a diagnosis. Use the symptom check, or contact a veterinarian.",

    'dose_refusal' => "I can't give a medication dose. A veterinarian has to decide that.",

    'limit_message' => "You've reached the message limit for this minute. Wait a moment, then try again.",

];
