document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        counter.innerText = '0'; // මුලින්ම 0 කරන්න
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / 30; // වේගය

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 40);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
});