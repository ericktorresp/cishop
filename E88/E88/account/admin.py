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

admin.site.unregister(User)
admin.site.register(User, UserFullAdmin)