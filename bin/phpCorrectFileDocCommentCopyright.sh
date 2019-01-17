#!/usr/bin/env bash

find ./*/ -not -path './vendor/*' -type f -name "*.php" -exec grep -q '@copyright' '{}' \; -print0 \
    | xargs -0 sed -i 's#@copyright Zicht Online <https\?://\(www\.\)\?zicht\.nl>#@copyright Zicht Online <https://zicht.nl>#i'

