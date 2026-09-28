import base64
import httpx

from ..core.config import get_settings

settings = get_settings()


async def analyze_image(data_url: str) -> dict:
    provider = settings.visual_search_provider.lower().strip()
    if provider == "none":
        return {
            "success": False,
            "error": "Visual search is disabled. Set VISUAL_SEARCH_PROVIDER=google_vision and configure GOOGLE_VISION_API_KEY."
        }

    if provider != "google_vision":
        return {"success": False, "error": f"Unsupported visual search provider: {provider}"}

    if not settings.google_vision_api_key:
        return {"success": False, "error": "Google Vision API key is not configured."}

    if "," not in data_url:
        return {"success": False, "error": "Expected a data URL image."}

    base64_data = data_url.split(",", 1)[1]
    payload = {
        "requests": [{
            "image": {"content": base64_data},
            "features": [
                {"type": "LABEL_DETECTION", "maxResults": 8},
                {"type": "TEXT_DETECTION", "maxResults": 2},
                {"type": "IMAGE_PROPERTIES", "maxResults": 1},
            ],
        }]
    }

    async with httpx.AsyncClient(timeout=30) as client:
        response = await client.post(
            "https://vision.googleapis.com/v1/images:annotate",
            params={"key": settings.google_vision_api_key},
            json=payload,
        )

    response.raise_for_status()
    result = response.json()
    if result.get("responses", [{}])[0].get("error"):
        return {"success": False, "error": result["responses"][0]["error"].get("message", "Vision API error.")}

    labels = result.get("responses", [{}])[0].get("labelAnnotations", [])
    keywords = [
        x["description"].lower()
        for x in labels
        if x.get("description", "").lower() not in {"clothing", "sari", "textile"}
    ]

    return {
        "success": True,
        "keywords": " ".join(keywords),
        "labels": labels,
    }
