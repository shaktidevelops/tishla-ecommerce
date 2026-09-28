from functools import lru_cache
from pathlib import Path
from pydantic_settings import BaseSettings, SettingsConfigDict

PROJECT_ROOT = Path(__file__).resolve().parents[3]
ENV_FILE = PROJECT_ROOT / ".env"

class Settings(BaseSettings):
    app_name: str = "Tishla Commerce API"
    app_env: str = "development"
    app_debug: bool = False
    database_url: str = "postgresql://postgres:CHANGE_ME@localhost:5432/tishla"
    database_min_size: int = 1
    database_max_size: int = 10
    jwt_secret: str = "CHANGE_ME_IN_PRODUCTION"
    jwt_exp_minutes: int = 30
    refresh_exp_days: int = 30
    cors_origins: str = "http://localhost:3000,http://127.0.0.1:3000"
    public_base_url: str = "http://localhost:3000"
    support_whatsapp: str = "919574716712"
    support_email: str = "purnikasales@gmail.com"
    default_gst_rate: float = 5.0
    default_shipping_charge: float = 0.0
    visual_search_provider: str = "none"
    google_vision_api_key: str = ""
    razorpay_key_id: str = ""
    razorpay_key_secret: str = ""
    razorpay_webhook_secret: str = ""
    admin_bootstrap_email: str = "purnikasales@gmail.com"
    admin_bootstrap_name: str = "Tishla Admin"
    admin_bootstrap_password: str = ""
    model_config = SettingsConfigDict(env_file=ENV_FILE, env_file_encoding="utf-8", case_sensitive=False, extra="ignore")
    @property
    def cors_origin_list(self) -> list[str]:
        return [v.strip() for v in self.cors_origins.split(",") if v.strip()]

@lru_cache
def get_settings() -> Settings:
    return Settings()
