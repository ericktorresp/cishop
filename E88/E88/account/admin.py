# coding=utf-8

from account.models import *
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin
from django.contrib.auth.models import User

class UserProfileInline(admin.StackedInline):
    model = UserProfile
    can_delete = False
    
class UserCardInline(admin.StackedInline):
    model = UserCard
    extra = 3
    
class UserFullAdmin(UserAdmin):
    inlines = (
        UserProfileInline,
        UserCardInline,
    )

class UserAccountDetailTypeAdmin(admin.ModelAdmin):
    list_display = ['name', 'operation']
    ordering = ('id',)
    
class UserAccountDetailAdmin(admin.ModelAdmin):
    list_display = ('from_user', 'detail_type', 'amount', 'db_time')
    list_filter = ('detail_type',)
    
admin.site.unregister(User)
admin.site.register(User, UserFullAdmin)
admin.site.register(UserAccountDetailType, UserAccountDetailTypeAdmin)
admin.site.register(UserAccountDetail, UserAccountDetailAdmin)
