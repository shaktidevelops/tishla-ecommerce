# Windows script policy

Normal Tishla development does not require PowerShell script execution. Use the `.cmd` launchers:

- `START_TISHLA_DEV.cmd` — full local stack
- `START_TISHLA_FRONTEND.cmd` — frontend-only visual preview
- `SET_GIT_IDENTITY.cmd` — configure Git identity
- `GIT_PUSH_WINDOWS.cmd` — commit and push

This avoids Windows execution-policy/signing restrictions that can block `.ps1` files.
