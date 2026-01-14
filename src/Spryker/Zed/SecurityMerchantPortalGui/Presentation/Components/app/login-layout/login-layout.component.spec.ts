import { Component, NO_ERRORS_SCHEMA } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { CardModule } from '@spryker/card';
import { LogoModule } from '@spryker/logo';
import { LoginLayoutComponent } from './login-layout.component';

@Component({
    template: `
        <mp-login-layout>
            <span title></span>
            <span class="default-slot"></span>
        </mp-login-layout>
    `,
    standalone: false,
})
class TestHostComponent {}

describe('LoginLayoutComponent', () => {
    let fixture: ComponentFixture<TestHostComponent>;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [LoginLayoutComponent, TestHostComponent],
            imports: [CardModule, LogoModule],
            schemas: [NO_ERRORS_SCHEMA],
        });

        fixture = TestBed.createComponent(TestHostComponent);
    });

    it('should render <spy-logo> component', () => {
        fixture.detectChanges();
        const logoComponent = fixture.debugElement.query(By.css('spy-logo'));

        expect(logoComponent).toBeTruthy();
    });

    it('should render <spy-card> component', () => {
        fixture.detectChanges();
        const cardComponent = fixture.debugElement.query(By.css('spy-card'));

        expect(cardComponent).toBeTruthy();
    });

    it('should render `title` slot to the `.ant-card-head-title` element', () => {
        fixture.detectChanges();
        const titleSlot = fixture.debugElement.query(By.css('.ant-card-head-title [title]'));

        expect(titleSlot).toBeTruthy();
    });

    it('should render default slot to the `.ant-card-body` element', () => {
        fixture.detectChanges();
        const defaultSlot = fixture.debugElement.query(By.css('.ant-card-body .default-slot'));

        expect(defaultSlot).toBeTruthy();
    });
});
