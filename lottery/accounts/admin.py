from mysite.accounts.models import *
from django.contrib import admin

class AccountTypeAdmin(admin.ModelAdmin):
    list_display = ('description', 'title')
    search_fields = ['description']

class AccountAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not obj.id:
            obj.client_ip = request.META['REMOTE_ADDR']
            obj.proxy_ip = request.META.get('HTTP_X_FORWARDED_FOR', '')
        obj.save()
    
admin.site.register(Account, AccountAdmin)
admin.site.register(AccountType, AccountTypeAdmin)
