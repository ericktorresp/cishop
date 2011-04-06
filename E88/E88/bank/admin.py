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
        return super(BankAdmin,self).formfield_for_dbfield(db_field, **kwargs)
    list_display = ('name', 'code', 'img_logo', )

class CardAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not change:
            obj.adder = request.user
        obj.save()

class ThirdpartAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop('request', None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(ThirdpartAdmin, self).formfield_for_dbfield(db_field, **kwargs)
    list_display = ('name', 'img_logo')

class ThirdpartAccountAdmin(admin.ModelAdmin):
    def save_model(self, request, obj, form, change):
        if not change:
            obj.adder = request.user
        obj.save()

class PaymentMethodAdmin(admin.ModelAdmin):
    def formfield_for_dbfield(self, db_field, **kwargs):
        if db_field.name == 'logo':
            request = kwargs.pop("request", None)
            kwargs['widget'] = ImageWidget
            return db_field.formfield(**kwargs)
        return super(PaymentMethodAdmin, self).formfield_for_dbfield(db_field, **kwargs)
    
admin.site.register(Bank, BankAdmin)
admin.site.register(Card, CardAdmin)
admin.site.register(Thirdpart, ThirdpartAdmin)
admin.site.register(ThirdpartAccount, ThirdpartAccountAdmin)
admin.site.register(PayMethod, PaymentMethodAdmin)