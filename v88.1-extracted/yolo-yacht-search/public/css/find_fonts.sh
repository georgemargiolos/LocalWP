#!/bin/bash
for file in *.css; do
    if grep -q "font-family" "$file"; then
        echo "=========================================="
        echo "FILE: $file"
        echo "=========================================="
        grep -B 5 "font-family" "$file" | grep -E "^[^/]*\{|font-family"
        echo ""
    fi
done
