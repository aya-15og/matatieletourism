<?php
/**
 * inc/netball.php
 * Helper functions for encoding detailed netball match statistics.
 */

declare(strict_types=1);

/**
 * Build and validate structured details for a netball result submission.
 *
 * Expected $_POST['details_arr'] format:
 * [
 *   "quarters" => [
 *       ["home" => 12, "away" => 10],
 *       ["home" => 8,  "away" => 9],
 *       ...
 *   ],
 *   "home_goals" => [
 *       ["player_id" => 5, "minute" => 2],
 *       ...
 *   ],
 *   "notes" => "MVP: #7"
 * ]
 */
function build_netball_details(array $input): string {
    $details = [
        'quarters'    => [],
        'home_goals'  => [],
        'away_goals'  => [],
        'notes'       => ''
    ];

    // --- Quarters ---
    if (!empty($input['quarters']) && is_array($input['quarters'])) {
        foreach ($input['quarters'] as $q) {
            $home = isset($q['home']) ? (int)$q['home'] : 0;
            $away = isset($q['away']) ? (int)$q['away'] : 0;
            $details['quarters'][] = ['home' => $home, 'away' => $away];
        }
    }

    // --- Home goals ---
    if (!empty($input['home_goals']) && is_array($input['home_goals'])) {
        foreach ($input['home_goals'] as $goal) {
            $pid = isset($goal['player_id']) ? (int)$goal['player_id'] : null;
            $min = isset($goal['minute']) ? (int)$goal['minute'] : null;
            if ($pid !== null && $min !== null) {
                $details['home_goals'][] = ['player_id' => $pid, 'minute' => $min];
            }
        }
    }

    // --- Away goals (optional future extension) ---
    if (!empty($input['away_goals']) && is_array($input['away_goals'])) {
        foreach ($input['away_goals'] as $goal) {
            $pid = isset($goal['player_id']) ? (int)$goal['player_id'] : null;
            $min = isset($goal['minute']) ? (int)$goal['minute'] : null;
            if ($pid !== null && $min !== null) {
                $details['away_goals'][] = ['player_id' => $pid, 'minute' => $min];
            }
        }
    }

    // --- Notes ---
    if (!empty($input['notes'])) {
        $details['notes'] = trim(strip_tags($input['notes']));
    }

    return json_encode($details, JSON_UNESCAPED_UNICODE);
}

/**
 * Pretty-print netball details for display (optional helper)
 */
function render_netball_details(?string $json): string {
    if (empty($json)) return '<em>No details</em>';
    $data = json_decode($json, true);
    if (!is_array($data)) return '<em>Invalid format</em>';

    $html = '';
    if (!empty($data['quarters'])) {
        $html .= '<strong>Quarters:</strong><br>';
        foreach ($data['quarters'] as $i => $q) {
            $html .= 'Q' . ($i + 1) . ': ' . $q['home'] . ' - ' . $q['away'] . '<br>';
        }
    }
    if (!empty($data['notes'])) {
        $html .= '<strong>Notes:</strong> ' . htmlspecialchars($data['notes']) . '<br>';
    }
    return $html;
}
