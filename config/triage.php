<?php

/*
| Rules run from top to bottom. The first match wins, so emergency rules
| stay above the milder ones. A critical symptom cannot be downgraded by
| anything selected with it.
*/

return [

    'disclaimer' => 'This is not a diagnosis. It is a safety screen from the symptoms you chose. A veterinarian has to examine your pet.',

    'species' => [
        'dog' => 'Dog',
        'cat' => 'Cat',
        'bird' => 'Bird',
        'rabbit' => 'Rabbit',
        'other' => 'Other',
    ],

    'symptoms' => [
        'vomiting' => 'Vomiting',
        'not_eating' => 'Not eating',
        'limping' => 'Limping',
        'difficulty_breathing' => 'Difficulty breathing',
        'seizure' => 'Seizure',
        'poisoning' => 'Poisoning',
        'bleeding' => 'Bleeding',
        'collapse' => 'Collapse',
        'pale_gums' => 'Pale gums',
        'straining_to_urinate' => 'Straining to urinate',
        'diarrhea' => 'Diarrhea',
        'lethargy' => 'Lethargy',
        'coughing' => 'Coughing',
        'itching' => 'Itching',
    ],

    'levels' => [
        'emergency' => [
            'title' => 'Emergency now',
            'tips' => [
                'Keep your pet in a quiet space and limit movement.',
                'Do not give human medicine, food, milk, or oil.',
                'Call the emergency line or the nearest clinic before you drive if someone can stay with your pet.',
            ],
        ],
        'soon' => [
            'title' => 'See a vet within 24 hours',
            'tips' => [
                'Offer fresh water and skip treats.',
                'Note when it started and whether it is getting worse.',
                'Keep your pet indoors. If a critical symptom appears, treat that as an emergency.',
            ],
        ],
        'monitor' => [
            'title' => 'Monitor at home',
            'tips' => [
                'Watch appetite, energy, and stool or litter for the rest of the day.',
                'Do not give human medicine.',
                'Call a vet if this is still going on tomorrow, or sooner if breathing, seizures, bleeding, or poisoning show up.',
            ],
        ],
    ],

    'rules' => [
        [
            'id' => 'breathing',
            'symptoms' => ['difficulty_breathing'],
            'level' => 'emergency',
            'reason' => 'Difficulty breathing is always an emergency, for every species.',
            'tip' => 'Do not cover the nose or put anything in the mouth. Keep the neck straight and the air cool.',
        ],
        [
            'id' => 'seizure',
            'symptoms' => ['seizure'],
            'level' => 'emergency',
            'reason' => 'A seizure is always an emergency, for every species.',
            'tip' => 'Clear the area and keep your hands away from the mouth. Note how long it lasts.',
        ],
        [
            'id' => 'poisoning',
            'symptoms' => ['poisoning'],
            'level' => 'emergency',
            'reason' => 'Possible poisoning is always an emergency, for every species.',
            'tip' => 'Do not make your pet vomit unless a veterinarian tells you to. Bring the package or plant with you.',
        ],
        [
            'id' => 'bleeding',
            'symptoms' => ['bleeding'],
            'level' => 'emergency',
            'reason' => 'Bleeding is treated as an emergency until a vet can see where it is coming from.',
            'tip' => 'Press a clean cloth on the wound. Do not use a tourniquet, and do not pull out anything that is stuck.',
        ],
        [
            'id' => 'collapse',
            'symptoms' => ['collapse'],
            'level' => 'emergency',
            'reason' => 'Collapse is always an emergency, for every species.',
            'tip' => 'Lay your pet on their side in a quiet place and keep them warm. Do not offer food or water.',
        ],
        [
            'id' => 'pale_gums',
            'symptoms' => ['pale_gums'],
            'level' => 'emergency',
            'reason' => 'Pale gums are always an emergency, for every species.',
            'tip' => 'Keep your pet still and warm while you get to a clinic.',
        ],
        [
            'id' => 'cat_urinary',
            'symptoms' => ['straining_to_urinate'],
            'species' => ['cat'],
            'level' => 'emergency',
            'reason' => 'A cat straining to urinate is always an emergency. A blocked bladder cannot wait.',
            'tip' => 'Do not press on the belly. Go to a clinic now.',
        ],
        [
            'id' => 'rabbit_not_eating',
            'symptoms' => ['not_eating'],
            'species' => ['rabbit'],
            'level' => 'emergency',
            'reason' => 'A rabbit that is not eating is always an emergency. Their gut can shut down quickly.',
            'tip' => 'Offer hay, but do not force-feed. Keep them warm and go to a clinic.',
        ],
        [
            'id' => 'straining',
            'symptoms' => ['straining_to_urinate'],
            'level' => 'soon',
            'reason' => 'Straining to urinate should be seen within 24 hours.',
            'tip' => 'Note whether any urine is passing. If none passes, or your pet is a cat, treat it as an emergency.',
        ],
        [
            'id' => 'vomiting',
            'symptoms' => ['vomiting'],
            'level' => 'soon',
            'reason' => 'Vomiting should be seen within 24 hours.',
            'tip' => 'Take water away for an hour if vomiting is active, then offer small sips. Do not give human stomach medicine.',
        ],
        [
            'id' => 'cat_not_eating',
            'symptoms' => ['not_eating'],
            'species' => ['cat'],
            'level' => 'soon',
            'reason' => 'A cat that is not eating should be seen within 24 hours.',
            'tip' => 'Note the last meal. Cats should not go a full day without food.',
        ],
        [
            'id' => 'bird_not_eating',
            'symptoms' => ['not_eating'],
            'species' => ['bird'],
            'level' => 'soon',
            'reason' => 'A bird that is not eating should be seen within 24 hours.',
            'tip' => 'Keep the bird warm and quiet. Offer the usual food, and do not force it.',
        ],
        [
            'id' => 'limping',
            'symptoms' => ['limping'],
            'level' => 'soon',
            'reason' => 'Limping should be seen within 24 hours.',
            'tip' => 'Rest your pet. Do not give human pain medicine, and do not wrap the leg unless a vet shows you how.',
        ],
        [
            'id' => 'lethargy',
            'symptoms' => ['lethargy'],
            'level' => 'soon',
            'reason' => 'Low energy should be seen within 24 hours.',
            'tip' => 'Keep a note of water intake and gum color. Pale gums change this to an emergency.',
        ],
        [
            'id' => 'coughing',
            'symptoms' => ['coughing'],
            'level' => 'soon',
            'reason' => 'Coughing should be seen within 24 hours.',
            'tip' => 'Keep your pet rested and away from smoke or strong scents. Trouble breathing changes this to an emergency.',
        ],
        [
            'id' => 'not_eating',
            'symptoms' => ['not_eating'],
            'level' => 'monitor',
            'reason' => 'A skipped meal can be watched at home when no urgent symptom is selected.',
            'tip' => 'Offer the usual food later today. If the next meal is refused, call a vet.',
        ],
        [
            'id' => 'diarrhea',
            'symptoms' => ['diarrhea'],
            'level' => 'monitor',
            'reason' => 'One bout of diarrhea can be watched at home when your pet is otherwise themselves.',
            'tip' => 'Offer water. If there is blood, or your pet becomes flat, treat it as an emergency.',
        ],
        [
            'id' => 'itching',
            'symptoms' => ['itching'],
            'level' => 'monitor',
            'reason' => 'Itching without another urgent symptom can be watched at home.',
            'tip' => 'Stop new treats or shampoos. Do not use human creams or essential oils.',
        ],
        [
            'id' => 'fallback',
            'symptoms' => [],
            'level' => 'monitor',
            'reason' => 'Nothing you selected matches an urgent rule. Watch at home and call a vet if it changes.',
            'tip' => null,
        ],
    ],

];
