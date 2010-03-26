from mysite.records.models import *
from django.contrib import admin

class ExpandCodeInline(admin.TabularInline):
    model = ExpandCode
    extra = 1
    
class ProjectAdmin(admin.ModelAdmin):
    inlines = [ExpandCodeInline]

class TaskDetailInline(admin.TabularInline):
    model = TaskDetail
    extra = 1
    
class TaskAdmin(admin.ModelAdmin):
    inlines = [TaskDetailInline]

admin.site.register(Order)
admin.site.register(Task, TaskAdmin)
admin.site.register(Project, ProjectAdmin)
