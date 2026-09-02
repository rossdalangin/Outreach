<?php
namespace CloseClient\Outreach\Integrations\AI;

if (!defined('ABSPATH')) {
    exit;
}

use CloseClient\Outreach\Includes\Models\Activity_Log;

class AI_Service {

    private static function get_provider() {
        $settings = get_option('cc_outreach_settings', array());
        $provider_type = !empty($settings['ai_provider']) ? $settings['ai_provider'] : 'openai';

        if ($provider_type === 'anthropic') {
            return new Anthropic_Provider($settings);
        }

        if ($provider_type === 'gemini') {
            return new Gemini_Provider($settings);
        }

        return new OpenAI_Provider($settings);
    }

    /**
     * AI Lead Fit Scoring (0-100)
     */
    public static function score_lead($lead) {
        $provider = self::get_provider();

        $system_prompt = "You are a lead qualification specialist for CloseClient. "
            . "Evaluate the lead against CloseClient's ideal customer profile (coaches, consultants, agencies needing website redesign & lead gen). "
            . "Output JSON with keys: 'score' (integer 0-100), 'reasoning' (1-2 sentences).";

        $prompt = sprintf(
            "Lead Name: %s %s\nCompany: %s\nWebsite: %s\nNiche: %s\nNotes: %s",
            $lead['first_name'], $lead['last_name'], $lead['company_name'],
            $lead['website'], $lead['niche'], $lead['notes']
        );

        $result = $provider->generate($prompt, $system_prompt);

        if (is_wp_error($result)) {
            // Rule-based heuristic score fallback
            $score = 70;
            if (stripos($lead['niche'], 'coach') !== false || stripos($lead['niche'], 'consultant') !== false) {
                $score += 20;
            }
            if (!empty($lead['website'])) {
                $score += 5;
            }
            return array('score' => min($score, 100), 'reasoning' => 'Heuristic fit score based on target coaching/consultant niche match.');
        }

        $decoded = json_decode($result, true);
        if ($decoded && isset($decoded['score'])) {
            return $decoded;
        }

        return array('score' => 75, 'reasoning' => $result);
    }

    /**
     * Research & analyze lead information
     */
    public static function research_lead($lead) {
        $provider = self::get_provider();

        $system_prompt = "You are a senior growth strategist and web developer at CloseClient. "
            . "Analyze the lead data provided. Identify likely business challenges, website/funnel opportunities, "
            . "and the best CloseClient service angle (WordPress web design, lead gen systems, SEO, conversion optimization) for this prospect. "
            . "Do not invent facts not in the data.";

        $lead_info = sprintf(
            "Name: %s %s\nCompany: %s\nWebsite: %s\nNiche: %s\nLocation: %s\nLead Source: %s\nNotes: %s",
            $lead['first_name'], $lead['last_name'], $lead['company_name'],
            $lead['website'], $lead['niche'], $lead['location'],
            $lead['lead_source'], $lead['notes']
        );

        $prompt = "Please provide a brief research summary (3-4 bullet points) and recommended outreach strategy for this prospect:\n\n" . $lead_info;

        $result = $provider->generate($prompt, $system_prompt);

        if (is_wp_error($result)) {
            // Return helpful mock fallback if API key is missing during local demo/test
            return "Analysis (Simulated/Fallback): Target client in " . esc_html($lead['niche']) . " niche. Focus on WordPress redesign and high-converting landing pages to boost client bookings.";
        }

        return $result;
    }

    /**
     * Generate personalized email draft
     */
    public static function generate_email_draft($lead, $type = 'first_contact') {
        $provider = self::get_provider();

        $seed = wp_generate_password(8, false);
        $system_prompt = "You are an expert personalized outreach specialist at CloseClient. "
            . "Write a warm, concise, natural, non-pushy email draft targeting coaches or consultants.\n"
            . "ANTI-SPAM REPHRASING MANDATE (Unique Variation Seed: $seed):\n"
            . "- Every email MUST use completely unique sentence structures, vocabulary, and phrasing to ensure 100% deliverability and avoid spam filters.\n"
            . "- Vary opening greetings, value propositions, and closing call-to-actions.\n"
            . "- Never invent false claims or pretend you saw specific features if not provided.\n"
            . "- Keep email short (100-140 words).\n"
            . "- Focus on offering value and starting a conversation.\n"
            . "- Respond in JSON format with keys: 'subject', 'body', 'rationale'.";

        $lead_info = sprintf(
            "First Name: %s\nLast Name: %s\nCompany: %s\nWebsite: %s\nNiche: %s\nNotes: %s",
            $lead['first_name'], $lead['last_name'], $lead['company_name'],
            $lead['website'], $lead['niche'], $lead['notes']
        );

        $prompt = "Generate a personalized outreach email draft for this lead:\n" . $lead_info;

        $response = $provider->generate($prompt, $system_prompt);

        if (is_wp_error($response)) {
            // Return structured fallback draft
            return array(
                'subject'   => sprintf("Quick question regarding %s's website & client growth", !empty($lead['company_name']) ? $lead['company_name'] : 'your business'),
                'body'      => sprintf("Hi %s,\n\nI was reviewing your website (%s) and loved your work in the %s coaching space.\n\nAt CloseClient, we help coaches and consultants build high-converting WordPress systems that consistently attract qualified clients.\n\nWould you be open to a brief 5-minute chat to share a few quick ideas for optimizing your current site?\n\nBest regards,\nCloseClient Team", $lead['first_name'], $lead['website'], $lead['niche']),
                'rationale' => "Tailored outreach focusing on high-converting WordPress systems for coaches/consultants."
            );
        }

        // Try decoding JSON
        $decoded = json_decode($response, true);
        if ($decoded && isset($decoded['subject']) && isset($decoded['body'])) {
            return $decoded;
        }

        // Fallback if raw text returned
        return array(
            'subject'   => sprintf("Idea for %s", $lead['company_name'] ? $lead['company_name'] : $lead['first_name']),
            'body'      => $response,
            'rationale' => "AI-generated personalized draft."
        );
    }

    /**
     * Analyze received conversation reply
     */
    public static function analyze_reply($reply_content, $lead) {
        $provider = self::get_provider();

        $system_prompt = "You are an AI conversation analyst for CloseClient CRM. "
            . "Analyze the prospect reply email. Determine sentiment (interested, neutral, not_interested, unsubscribed, meeting_requested) "
            . "and output a concise summary and recommended next step in JSON with keys: 'sentiment', 'summary', 'recommended_action'.";

        $prompt = "Prospect: " . $lead['first_name'] . " " . $lead['last_name'] . "\nReply Content:\n" . $reply_content;

        $result = $provider->generate($prompt, $system_prompt);

        if (is_wp_error($result)) {
            return array(
                'sentiment'          => 'neutral',
                'summary'            => 'Received prospect reply.',
                'recommended_action' => 'Review reply and send manual response.'
            );
        }

        $decoded = json_decode($result, true);
        if ($decoded && isset($decoded['sentiment'])) {
            return $decoded;
        }

        return array(
            'sentiment'          => 'interested',
            'summary'            => $result,
            'recommended_action' => 'Follow up with meeting availability.'
        );
    }
}
