<?php
/**
 * Generate a round-robin schedule safely.
 *
 * @param array  $teams        Array of team IDs
 * @param string $start        Starting datetime 'YYYY-MM-DD HH:MM:SS'
 * @param int    $rounds       Number of rounds (1=single, 2=double)
 * @param array  $venueMap     Optional map of team pair to venue ['1_2'=>'Stadium A']
 * @param int    $matchDuration Duration of each match in minutes (default 60)
 * @param int    $interval      Minutes between matches (default 30)
 *
 * @return array List of matches ['home'=>id,'away'=>id,'date'=>'YYYY-MM-DD HH:MM:SS','venue'=>'']
 */
function round_robin_schedule(array $teams, string $start, int $rounds = 1, array $venueMap = [], int $matchDuration = 60, int $interval = 30): array {
    $numTeams = count($teams);
    $isOdd = $numTeams % 2 !== 0;

    // Add dummy "bye" if odd, but we will skip it in final matches
    if ($isOdd) {
        $teams[] = 0; // use 0 as a placeholder
        $numTeams++;
    }

    $half = $numTeams / 2;
    $matches = [];
    $currentTime = new DateTime($start);

    // Repeat for the number of rounds
    for ($round = 0; $round < $rounds; $round++) {
        $rotating = $teams;

        for ($r = 0; $r < $numTeams - 1; $r++) {
            for ($i = 0; $i < $half; $i++) {
                $home = $rotating[$i];
                $away = $rotating[$numTeams - 1 - $i];

                // Skip byes (team ID 0)
                if ($home === 0 || $away === 0) {
                    continue;
                }

                // Determine venue
                $pairKey = $home < $away ? "{$home}_{$away}" : "{$away}_{$home}";
                $venue = $venueMap[$pairKey] ?? '';

                // Add match
                $matches[] = [
                    'home' => $home,
                    'away' => $away,
                    'date' => $currentTime->format('Y-m-d H:i:s'),
                    'venue' => $venue,
                ];

                // Increment time for next match
                $currentTime->modify("+".($matchDuration + $interval)." minutes");
            }

            // Rotate teams for next round (except the first team)
            $rotating = array_merge(
                [$rotating[0]],
                [end($rotating)],
                array_slice($rotating, 1, -1)
            );
        }
    }

    return $matches;
}
