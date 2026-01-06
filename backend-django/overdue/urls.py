from django.urls import path
from . import views

urlpatterns = [
    path('overdue/mark', views.mark_overdue_tasks, name='mark_overdue'),
    path('tasks/<int:task_id>/validate-status', views.validate_task_status, name='validate_status'),
    path('health', views.health_check, name='health'),
]

