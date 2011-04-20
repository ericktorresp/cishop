# coding=utf-8

from bank.models import *
from django.contrib import admin

from home.widgets import ImageWidget

class BankAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(BankAdmin, self).formfield_for_dbfield(db_field, **kwargs)
    list_display = ('name', 'code', 'img_logo',)

class CardAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not change:
            obj.adder = request.user
        obj.save()

class DepositMethodAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not change:
            obj.adder = request.user
        obj.save()
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(DepositMethodAdmin, self).formfield_for_dbfield(db_field, **kwargs)
    list_display = ('name', 'alias', 'img_logo',)
    
class DepositMethodAccountAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not change:
            obj.adder = request.user
        obj.save()
    list_display = ('login_name', 'enabled',)
    list_filter = ['deposit_method', 'enabled']
    search_fields = ['login_name', 'email', 'account_name']
        
class CellphoneAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not change:
            obj.adder = request.user
        obj.save()
    list_display = ('number', 'enabled',)
    list_filter = ['enabled']

class DepositLogAdmin(admin.ModelAdmin):
    list_display = ('order_number', 'user', 'amount', 'cellphone', 'deposit_time', 'status')

class SmsLogAdmin(admin.ModelAdmin):
    list_display = ('sender', 'receiver', 'receive_time')

admin.site.register(Bank, BankAdmin)
admin.site.register(Card, CardAdmin)
admin.site.register(DepositMethod, DepositMethodAdmin)
admin.site.register(DepositMethodAccount, DepositMethodAccountAdmin)
admin.site.register(Cellphone, CellphoneAdmin)
admin.site.register(DepositLog, DepositLogAdmin)
admin.site.register(SmsLog, SmsLogAdmin)
