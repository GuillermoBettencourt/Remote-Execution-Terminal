#include <stdio.h>

int main() {
    int input;
    int number;

    while (1) {
        printf("Menu:\r\n1. Insert a number\r\n2. Exit\r\n");
        fflush(stdout);
        scanf("%d", &input);

        switch (input) {
            case 1:
                printf("Enter a number: ");
                fflush(stdout);
                scanf("%d", &number);
                printf("You entered: %d\r\n", number);
                break;
            case 2:
                printf("Exiting the program.\r\n");
                return 0;
            default:
                printf("Invalid choice. Please try again.\r\n");
        }
        fflush(stdout);
    }
}