<footer class="app-footer">
    <!-- <div class="float-end d-none d-sm-inline">İstediğin her şey</div> -->
    <strong>
        Copyright &copy; 2004-<?php echo date('Y') ?> &nbsp;
        <a href="https://www.proexams.net" class="text-decoration-none">TimePay - Bilal Sami Zahit ÖZGÜL</a>
    </strong>
    Tüm hakları saklıdır.
</footer>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"
    integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
    integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous">
</script>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="dist/js/adminlte.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap5.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>


<!-- FullCalendar JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/tr.js'></script>


<script>
const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
const Default = {
    scrollbarTheme: "os-theme-light",
    scrollbarAutoHide: "leave",
    scrollbarClickScroll: true,
};
document.addEventListener("DOMContentLoaded", function() {
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
    if (
        sidebarWrapper &&
        typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
    ) {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: Default.scrollbarTheme,
                autoHide: Default.scrollbarAutoHide,
                clickScroll: Default.scrollbarClickScroll,
            },
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
    integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>
<script>
const connectedSortables =
    document.querySelectorAll(".connectedSortable");
connectedSortables.forEach((connectedSortable) => {
    let sortable = new Sortable(connectedSortable, {
        group: "shared",
        handle: ".card-header",
    });
});

const cardHeaders = document.querySelectorAll(
    ".connectedSortable .card-header",
);
cardHeaders.forEach((cardHeader) => {
    cardHeader.style.cursor = "move";
});
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"
    integrity="sha256-+vh8GkaU7C9/wbSLIcwq82tQ2wTf44aOHA8HlBMwRI8=" crossorigin="anonymous"></script>
<!-- ChartJS -->
<script>
const sales_chart_options = {
    series: [{
            name: "Digital Goods",
            data: [28, 48, 40, 19, 86, 27, 90],
        },
        {
            name: "Electronics",
            data: [65, 59, 80, 81, 56, 55, 40],
        },
    ],
    chart: {
        height: 300,
        type: "area",
        toolbar: {
            show: false,
        },
    },
    legend: {
        show: false,
    },
    colors: ["#0d6efd", "#20c997"],
    dataLabels: {
        enabled: false,
    },
    stroke: {
        curve: "smooth",
    },
    xaxis: {
        type: "datetime",
        categories: [
            "2023-01-01",
            "2023-02-01",
            "2023-03-01",
            "2023-04-01",
            "2023-05-01",
            "2023-06-01",
            "2023-07-01",
        ],
    },
    tooltip: {
        x: {
            format: "MMMM yyyy",
        },
    },
};

const sales_chart = new ApexCharts(
    document.querySelector("#revenue-chart"),
    sales_chart_options,
);
sales_chart.render();
</script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"
    integrity="sha256-/t1nN2956BT869E6H4V1dnt0X5pAQHPytli+1nTZm2Y=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"
    integrity="sha256-XPpPaZlU8S/HWf7FZLAncLg2SAkP8ScUTII89x9D3lY=" crossorigin="anonymous"></script>
<script>
const visitorsData = {
    US: 398,
    SA: 400,
    CA: 1000,
    DE: 500,
    FR: 760,
    CN: 300,
    AU: 700,
    BR: 600,
    IN: 800,
    GB: 320,
    RU: 3000,
};

const map = new jsVectorMap({
    selector: "#world-map",
    map: "world",
});

const option_sparkline1 = {
    series: [{
        data: [1000, 1200, 920, 927, 931, 1027, 819, 930, 1021],
    }, ],
    chart: {
        type: "area",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    stroke: {
        curve: "straight",
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0,
    },
    colors: ["#DCE6EC"],
};

const sparkline1 = new ApexCharts(
    document.querySelector("#sparkline-1"),
    option_sparkline1,
);
sparkline1.render();

const option_sparkline2 = {
    series: [{
        data: [515, 519, 520, 522, 652, 810, 370, 627, 319, 630, 921],
    }, ],
    chart: {
        type: "area",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    stroke: {
        curve: "straight",
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0,
    },
    colors: ["#DCE6EC"],
};

const sparkline2 = new ApexCharts(
    document.querySelector("#sparkline-2"),
    option_sparkline2,
);
sparkline2.render();

const option_sparkline3 = {
    series: [{
        data: [15, 19, 20, 22, 33, 27, 31, 27, 19, 30, 21],
    }, ],
    chart: {
        type: "area",
        height: 50,
        sparkline: {
            enabled: true,
        },
    },
    stroke: {
        curve: "straight",
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0,
    },
    colors: ["#DCE6EC"],
};

const sparkline3 = new ApexCharts(
    document.querySelector("#sparkline-3"),
    option_sparkline3,
);
sparkline3.render();
</script>

</body>
</html>