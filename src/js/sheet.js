
import { clamp } from './ui.js';

const SHEET_TOP_Y = 5;
const SHEET_MID_Y = 25;
const SHEET_CLOSED_Y = 100;
const SWIPE_THRESHOLD_PX = 12;
const VELOCITY_TRIGGER = 0.3;

let isOpen = false;
let currentY = 100;

const drag = {
  active: false,
  startY: 0,
  lastY: 0,
  startSheetY: SHEET_CLOSED_Y,
  lastT: 0,
  velocity: 0,
  lockedByScroll: false,
  pointerId: null,
};

const setY = (pct, sheet, openBtn) => {
  currentY = clamp(pct, SHEET_TOP_Y, SHEET_CLOSED_Y);
  document.documentElement.style.setProperty('--sheet-y', `${currentY}%`);
  const isHidden = currentY === SHEET_CLOSED_Y;
  sheet.setAttribute('aria-hidden', String(isHidden));
  openBtn.setAttribute('aria-expanded', String(!isHidden));
};

export const openSheetMid = (sheet, openBtn) => {
  isOpen = true;
  setY(SHEET_MID_Y, sheet, openBtn);
};

export const closeSheet = (sheet, openBtn) => {
  isOpen = false;
  setY(SHEET_CLOSED_Y, sheet, openBtn);
};

function dragStart(y, pointerId, sheet) {
  drag.active = true;
  drag.startY = y;
  drag.lastY = y;
  drag.startSheetY = currentY;
  drag.lastT = performance.now();
  drag.velocity = 0;
  drag.pointerId = pointerId ?? null;
  sheet.style.transition = 'none';
  // Apply to both body and html for robustness
  document.body.style.overscrollBehaviorY = 'contain';
  document.documentElement.style.overscrollBehaviorY = 'contain';
}

function dragMove(y, sheet, openBtn) {
  if (!drag.active) return;
  const now = performance.now();
  const dy = y - drag.lastY;
  const dt = Math.max(1, now - drag.lastT);
  drag.velocity = dy / dt;
  const h = window.innerHeight;
  const delta = ((y - drag.startY) / h) * 100;
  const target = clamp(drag.startSheetY + delta, SHEET_TOP_Y, SHEET_CLOSED_Y);
  setY(target, sheet, openBtn);
  drag.lastY = y;
  drag.lastT = now;
}

function dragEnd(sheet, openBtn) {
  if (!drag.active) return;
  sheet.style.transition = '';
  // Remove from both
  document.body.style.overscrollBehaviorY = '';
  document.documentElement.style.overscrollBehaviorY = '';

  const velocity = drag.velocity;
  const isSwipeUp = velocity < -VELOCITY_TRIGGER;
  const isSwipeDown = velocity > VELOCITY_TRIGGER;

  if (isSwipeUp) {
    if (currentY > SHEET_MID_Y) {
      setY(SHEET_MID_Y, sheet, openBtn);
    } else {
      setY(SHEET_TOP_Y, sheet, openBtn);
    }
    isOpen = true;
  } else if (isSwipeDown) {
    // New simplified logic: always close on swipe down.
    closeSheet(sheet, openBtn);
  } else {
    const states = [SHEET_TOP_Y, SHEET_MID_Y, SHEET_CLOSED_Y];
    const closestState = states.reduce((prev, curr) => {
      return Math.abs(curr - currentY) < Math.abs(prev - currentY)
        ? curr
        : prev;
    });
    setY(closestState, sheet, openBtn);
    isOpen = closestState !== SHEET_CLOSED_Y;
  }

  drag.active = false;
  drag.lockedByScroll = false;
  drag.pointerId = null;
}

export const initSheet = (sheet, openBtn, sheetScroll) => {
  setY(SHEET_CLOSED_Y, sheet, openBtn);

  openBtn.onclick = () => {
    isOpen ? closeSheet(sheet, openBtn) : openSheetMid(sheet, openBtn);
  };

  sheet.addEventListener('pointerdown', (e) => {
    if (e.target.closest('button')) return;
    const y = e.clientY;
    if (
      isOpen &&
      currentY === SHEET_TOP_Y &&
      sheetScroll &&
      sheetScroll.scrollTop > 0
    ) {
      drag.lockedByScroll = true;
      drag.startY = y;
      drag.pointerId = e.pointerId;
      return;
    }
    e.preventDefault();
    sheet.setPointerCapture(e.pointerId);
    dragStart(y, e.pointerId, sheet);
  });

  sheet.addEventListener('pointermove', (e) => {
    if (drag.lockedByScroll) {
      const dy = e.clientY - drag.startY;
      if (
        dy > SWIPE_THRESHOLD_PX &&
        sheetScroll &&
        sheetScroll.scrollTop <= 0
      ) {
        drag.lockedByScroll = false;
        try {
          sheet.setPointerCapture(drag.pointerId ?? e.pointerId);
        } catch {}
        e.preventDefault();
        dragStart(e.clientY, e.pointerId, sheet);
      }
      return;
    }
    dragMove(e.clientY, sheet, openBtn);
  });

  sheet.addEventListener('pointerup', () => dragEnd(sheet, openBtn));
  sheet.addEventListener('pointercancel', () => dragEnd(sheet, openBtn));
};
