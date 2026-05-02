
Fuzzing usually = random input. For logic testing, ask:

- What does this parameter _mean_?

Examples:

- `role=user` → try `admin`, `null`, `-1`
- `price=100` → try `0`, `-100`, `999999`
- `user_id=123` → try accessing others’ data (IDOR)

This is closer to **semantic fuzzing** rather than blind fuzzing.

