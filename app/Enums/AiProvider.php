<?php

namespace App\Enums;

enum AiProvider: string
{
    case Gemini = 'gemini';
    case OpenAI = 'openai';
    case Anthropic = 'anthropic';

    public function label(): string
    {
        return match ($this) {
            self::Gemini => 'Google Gemini',
            self::OpenAI => 'OpenAI',
            self::Anthropic => 'Anthropic (Claude)',
        };
    }

    public function models(): array
    {
        return match ($this) {
            self::Gemini => [
                'auto' => 'Auto',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash (Free)',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro',
                'gemini-3-flash-preview' => 'Gemini 3 Flash Preview',
            ],
            self::OpenAI => [
                'auto' => 'Auto',
                'gpt-5-nano' => 'GPT-5 Nano',
                'gpt-5.4-nano' => 'GPT-5.4 Nano',
                'gpt-5-mini' => 'GPT-5 Mini',
                'gpt-5.4-mini' => 'GPT-5.4 Mini',
                'gpt-5.2' => 'GPT-5.2',
                'gpt-5.4' => 'GPT-5.4',
                'gpt-5.2-pro' => 'GPT-5.2 Pro',
                'gpt-5.4-pro' => 'GPT-5.4 Pro',
                'o4-mini' => 'o4-mini (Reasoning)',
                'gpt-4o-mini' => 'GPT-4o Mini (Cheap)',
                'gpt-4o' => 'GPT-4o',
            ],
            self::Anthropic => [
                'auto' => 'Auto',
                'claude-haiku-4-5' => 'Claude Haiku (Fast)',
                'claude-sonnet-4-5' => 'Claude Sonnet',
            ],
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
