#!/usr/bin/env bash
# =============================================================================
#  SyncU — smoke testy endpointów HTTP
#  Użycie: bash tests/endpoints.sh [BASE_URL]
#  Domyślny BASE_URL: http://localhost:8080
#
#  Wymagania: curl, grep
#  Dane testowe z seed.sql: jan@example.com / Student1234!
# =============================================================================

set -uo pipefail

BASE_URL="${1:-http://localhost:8080}"
COOKIE_JAR=$(mktemp /tmp/syncu_cookies_XXXXXX.txt)
PASS=0
FAIL=0

# Kolory terminala
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

pass()  { echo -e "${GREEN}✓${NC}  $1"; PASS=$((PASS + 1)); }
fail()  { echo -e "${RED}✗${NC}  $1  →  $2"; FAIL=$((FAIL + 1)); }
info()  { echo -e "${YELLOW}→${NC}  $1"; }
title() { echo; echo "--- $1 ---"; }

check_status() {
    local name="$1" expected="$2" actual="$3"
    if [[ "$actual" -eq "$expected" ]]; then
        pass "$name  (HTTP $actual)"
    else
        fail "$name" "oczekiwano HTTP $expected, otrzymano HTTP $actual"
    fi
}

cleanup() { rm -f "$COOKIE_JAR"; }
trap cleanup EXIT

# =============================================================================
echo "============================================="
echo "  SyncU — testy endpointów"
echo "  URL: $BASE_URL"
echo "============================================="

# =============================================================================
title "1. Publiczne strony"

STATUS=$(curl -si -o /dev/null -w "%{http_code}" \
    -c "$COOKIE_JAR" "$BASE_URL/login")
check_status "GET /login — strona logowania" 200 "$STATUS"

STATUS=$(curl -si -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL/register")
check_status "GET /register — strona rejestracji" 200 "$STATUS"

# =============================================================================
title "2. Ochrona tras (bez sesji)"

STATUS=$(curl -si -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/dashboard")
check_status "GET /dashboard (brak sesji) — redirect na /login" 302 "$STATUS"

STATUS=$(curl -si -o /dev/null -w "%{http_code}" \
    -H "X-Requested-With: XMLHttpRequest" \
    -b "$COOKIE_JAR" "$BASE_URL/api/courses")
check_status "GET /api/courses (brak sesji, AJAX) — 401 Unauthorized" 401 "$STATUS"

# =============================================================================
title "3. Token CSRF"

LOGIN_HTML=$(curl -s -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL/login")
CSRF=$(echo "$LOGIN_HTML" | grep -oP '(?<=name="_csrf" value=")[^"]+' || true)

if [[ -n "$CSRF" ]]; then
    pass "Pobranie tokenu CSRF z formularza logowania"
    info "Token: ${CSRF:0:16}..."
else
    fail "Pobranie tokenu CSRF" "nie znaleziono pola _csrf w HTML"
    echo
    echo "UWAGA: dalsze testy wymagają sesji — przerywam."
    echo -e "${YELLOW}Wynik: $PASS zaliczonych, $FAIL niezaliczonych${NC}"
    exit 1
fi

# =============================================================================
title "4. Logowanie"

# Błędne dane — formularz powinien się przeładować (200)
STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -X POST "$BASE_URL/login" \
    --data-urlencode "_csrf=$CSRF" \
    --data-urlencode "email=zly@przyklad.pl" \
    --data-urlencode "password=wrongpassword")
check_status "POST /login (złe dane) — formularz z błędem" 200 "$STATUS"

# Pobierz świeży CSRF (po nieudanym logowaniu sesja może mieć nowy token)
LOGIN_HTML=$(curl -s -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL/login")
CSRF=$(echo "$LOGIN_HTML" | grep -oP '(?<=name="_csrf" value=")[^"]+' || true)

# Poprawne dane — redirect na /dashboard
STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -X POST "$BASE_URL/login" \
    --data-urlencode "_csrf=$CSRF" \
    --data-urlencode "email=jan@example.com" \
    --data-urlencode "password=Student1234!")
check_status "POST /login (poprawne dane) — redirect na dashboard" 302 "$STATUS"

# =============================================================================
title "5. Chronione endpointy API (po zalogowaniu)"

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/api/courses")
check_status "GET /api/courses — lista kursów" 200 "$STATUS"

BODY=$(curl -s -b "$COOKIE_JAR" "$BASE_URL/api/courses")
if echo "$BODY" | grep -q '"id"'; then
    pass "GET /api/courses — odpowiedź zawiera pole 'id' (JSON)"
else
    fail "GET /api/courses — brak pola 'id' w odpowiedzi" "$BODY"
fi

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/api/events")
check_status "GET /api/events — lista wydarzeń" 200 "$STATUS"

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/api/notes")
check_status "GET /api/notes — lista notatek" 200 "$STATUS"

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/api/study-progress")
check_status "GET /api/study-progress — postęp nauki" 200 "$STATUS"

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/api/shares/events")
check_status "GET /api/shares/events — udostępnione wydarzenia" 200 "$STATUS"

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" "$BASE_URL/api/shares/notes")
check_status "GET /api/shares/notes — udostępnione notatki" 200 "$STATUS"

# =============================================================================
title "6. Zabezpieczenie POST (brak CSRF)"

STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -X POST "$BASE_URL/courses/create" \
    -d "name=TestKurs&color=%23ff0000")
check_status "POST /courses/create (brak _csrf) — 403 Forbidden" 403 "$STATUS"

# =============================================================================
echo
echo "============================================="
if [[ $FAIL -eq 0 ]]; then
    echo -e "${GREEN}Wynik: $PASS/$((PASS + FAIL)) testów zaliczonych ✓${NC}"
else
    echo -e "${RED}Wynik: $PASS/$((PASS + FAIL)) testów zaliczonych ($FAIL niezaliczonych)${NC}"
fi
echo "============================================="

[[ $FAIL -eq 0 ]] && exit 0 || exit 1
