from fastapi import Depends, Header, HTTPException, status
import jwt

from ..core.security import decode_access_token


def get_current_user(authorization: str | None = Header(default=None)) -> dict:
    if not authorization or not authorization.lower().startswith("bearer "):
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Authentication required.")
    token = authorization.split(" ", 1)[1].strip()
    try:
        return decode_access_token(token)
    except jwt.PyJWTError as exc:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Invalid or expired token.") from exc


def require_role(role: str):
    def checker(user: dict = Depends(get_current_user)) -> dict:
        roles = user.get("roles", [])
        if role not in roles and "admin" not in roles:
            raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Insufficient permissions.")
        return user
    return checker
