from fastapi import APIRouter
from pydantic import BaseModel, Field

from ..services.visual_search import analyze_image

router = APIRouter(prefix="/api/visual-search", tags=["visual-search"])


class VisualSearchPayload(BaseModel):
    image: str = Field(min_length=20)


@router.post("")
async def visual_search(payload: VisualSearchPayload):
    return await analyze_image(payload.image)
