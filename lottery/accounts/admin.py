from mysite.accounts.models import *
from django.contrib import admin

class AccountTypeAdmin(admin.ModelAdmin):
    list_display = ('description', 'title')
    search_fields = ['description']
admin.site.register(AccountType, AccountTypeAdmin)
