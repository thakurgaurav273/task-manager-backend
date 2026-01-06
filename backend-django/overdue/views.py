from datetime import date, datetime
from django.db import connection
from rest_framework.decorators import api_view
from rest_framework.response import Response
from rest_framework import status


@api_view(['POST'])
def mark_overdue_tasks(request):
    """
    Mark tasks as OVERDUE if due_date is in the past and status is not DONE.
    """
    try:
        with connection.cursor() as cursor:
            cursor.execute("""
                UPDATE tasks 
                SET status = 'OVERDUE', updated_at = NOW()
                WHERE due_date < CURDATE() 
                AND status != 'DONE' 
                AND status != 'OVERDUE'
            """)
            updated_count = cursor.rowcount
            
        return Response({
            'success': True,
            'message': f'Marked {updated_count} tasks as OVERDUE',
            'updated_count': updated_count
        }, status=status.HTTP_200_OK)
    except Exception as e:
        return Response({
            'success': False,
            'message': str(e)
        }, status=status.HTTP_500_INTERNAL_SERVER_ERROR)


@api_view(['POST'])
def validate_task_status(request, task_id):
    """
    Validate task status change according to business rules.
    
    Business Rules:
    1. Overdue tasks cannot move back to IN_PROGRESS
    2. Only Admin can close overdue tasks (mark as DONE)
    3. If task is past due date, automatically mark as OVERDUE
    """
    try:
        current_status = request.data.get('current_status')
        new_status = request.data.get('new_status')
        due_date_str = request.data.get('due_date')
        is_admin = request.data.get('is_admin', False)
        
        if not due_date_str:
            return Response({
                'success': False,
                'message': 'due_date is required'
            }, status=status.HTTP_400_BAD_REQUEST)
        
        try:
            due_date = datetime.strptime(due_date_str, '%Y-%m-%d').date()
        except ValueError:
            return Response({
                'success': False,
                'message': 'Invalid date format. Expected YYYY-MM-DD'
            }, status=status.HTTP_400_BAD_REQUEST)
        
        today = date.today()
        
        if current_status == 'OVERDUE' and new_status == 'IN_PROGRESS':
            return Response({
                'success': False,
                'message': 'Overdue tasks cannot be moved back to IN_PROGRESS'
            }, status=status.HTTP_400_BAD_REQUEST)
        
        if current_status == 'OVERDUE' and new_status == 'DONE' and not is_admin:
            return Response({
                'success': False,
                'message': 'Only admins can close overdue tasks'
            }, status=status.HTTP_403_FORBIDDEN)
        
        if due_date < today and new_status != 'DONE' and new_status != 'OVERDUE':
            with connection.cursor() as cursor:
                cursor.execute("""
                    UPDATE tasks 
                    SET status = 'OVERDUE', updated_at = NOW()
                    WHERE id = %s
                """, [task_id])
            
            return Response({
                'success': True,
                'message': 'Task automatically marked as OVERDUE',
                'status': 'OVERDUE'
            }, status=status.HTTP_200_OK)
        
        return Response({
            'success': True,
            'message': 'Status change is valid'
        }, status=status.HTTP_200_OK)
        
    except Exception as e:
        return Response({
            'success': False,
            'message': str(e)
        }, status=status.HTTP_500_INTERNAL_SERVER_ERROR)


@api_view(['GET'])
def health_check(request):
    """
    Health check endpoint.
    """
    return Response({
        'success': True,
        'message': 'Django service is running'
    }, status=status.HTTP_200_OK)
